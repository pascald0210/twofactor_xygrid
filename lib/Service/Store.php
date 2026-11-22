<?php
declare(strict_types=1);
namespace OCA\TwoFactorXYGrid\Service;

use OCP\IConfig;
use OCP\IDBConnection;
use OCP\ISession;
use Psr\Log\LoggerInterface;

final class Store {
    public function __construct(
        private IDBConnection $db, private IConfig $config,
        private ISession $session, private LoggerInterface $logger,
    ) {}

    private function subject(string $uid): string { return hash('sha256',$uid); }
    private function revision(): string { return bin2hex(random_bytes(16)); }
    private function digest(string $uid,string $code): string {
        $key=$this->config->getSystemValueString('secret','');
        if ($key==='') { throw new \RuntimeException('Secret d’instance absent.'); }
        return hash_hmac('sha256',"xygrid:v1\0".$this->subject($uid)."\0".$code,$key);
    }
    private function binding(): string {
        $k='twofactor_xygrid.binding';
        $b=$this->session->get($k);
        if (!is_string($b) || strlen($b)!==64) {
            $b=bin2hex(random_bytes(32)); $this->session->set($k,$b);
        }
        return hash('sha256',$b);
    }
    public function audit(string $event,string $uid,bool $warn=false): void {
        $ctx=['app'=>'twofactor_xygrid','event'=>$event,'account'=>$uid];
        $this->logger->log($warn?'warning':'info','xygrid: {event}; account={account}',$ctx);
    }
    private function where($q,array $conditions): void {
        foreach ($conditions as $name=>$value) {
            $q->andWhere($q->expr()->eq($name,$q->createNamedParameter($value)));
        }
    }
    private function row(string $table,array $conditions): ?array {
        $q=$this->db->getQueryBuilder();
        $q->select('*')->from($table); $this->where($q,$conditions);
        $r=$q->executeQuery();
        try { return $r->fetchAssociative() ?: null; } finally { $r->closeCursor(); }
    }
    private function insert(string $table,array $fields): void {
        $q=$this->db->getQueryBuilder(); $q->insert($table);
        foreach ($fields as $k=>$v) { $q->setValue($k,$q->createNamedParameter($v)); }
        $q->executeStatement();
    }
    private function delete(string $table,array $conditions): int {
        $q=$this->db->getQueryBuilder(); $q->delete($table);
        $this->where($q,$conditions); return $q->executeStatement();
    }
    public function account(string $uid): ?array {
        return $this->row('xyg_account',['subject'=>$this->subject($uid)]);
    }
    public function purgeExpired(): int {
        $q=$this->db->getQueryBuilder();
        return $q->delete('xyg_ticket')->where($q->expr()->lte('expires_ts',
            $q->createNamedParameter(time())))->executeStatement();
    }
    public function issue(string $uid,string $purpose): array {
        if (!in_array($purpose,['login','enrol'],true)) { throw new \LogicException(); }
        $this->purgeExpired();
        $subject=$this->subject($uid); $binding=$this->binding();
        $scope=['subject'=>$subject,'binding'=>$binding,'purpose'=>$purpose];
        $this->delete('xyg_ticket',$scope);
        $id=bin2hex(random_bytes(24));
        $layout=Codec::layout();
        $board=['id'=>$id,'digits'=>str_split($layout)];
        if ($purpose==='enrol') {
            $code=sprintf('%06d',random_int(0,999999));
            $proof=$this->digest($uid,$code);
            $board['code']=$code;
        } else {
            $a=$this->account($uid);
            if (!$a || (int)$a['until_ts']>time()) { return []; }
            // Lie le ticket à l’enrôlement courant, pas seulement à l’UID.
            $proof=$a['verifier'];
        }
        $this->insert('xyg_ticket',$scope+[
            'ticket'=>hash('sha256',$id),'layout'=>$layout,'proof'=>$proof,
            'expires_ts'=>time()+300,
        ]);
        return $board;
    }
    private function consume(string $uid,string $purpose,string $wire): ?array {
        $parts=explode(':',$wire,2);
        if (!preg_match('/\A[a-f0-9]{48}\z/',$parts[0])) { return null; }
        $where=['ticket'=>hash('sha256',$parts[0]),'subject'=>$this->subject($uid),
            'binding'=>$this->binding(),'purpose'=>$purpose];
        $ticket=$this->row('xyg_ticket',$where);
        if (!$ticket) { return null; }
        // Un seul processus peut remporter cette suppression.
        if ($this->delete('xyg_ticket',$where)!==1 || (int)$ticket['expires_ts']<=time()) {
            return null;
        }
        $code=Codec::decode($ticket['layout'],$parts[1]??'');
        return ['proof'=>$ticket['proof'],'digest'=>$code===null?null:$this->digest($uid,$code)];
    }
    public function enrolProof(string $uid,string $wire): ?string {
        $t=$this->consume($uid,'enrol',$wire);
        if (!$t || $t['digest']===null || !hash_equals($t['proof'],$t['digest'])) { return null; }
        return $t['digest'];
    }
    public function create(string $uid,string $proof): void {
        $this->insert('xyg_account',['subject'=>$this->subject($uid),'verifier'=>$proof,
            'revision'=>$this->revision(),'failures'=>0,'until_ts'=>0]);
    }
    public function erase(string $uid): void {
        $where=['subject'=>$this->subject($uid)];
        $this->delete('xyg_ticket',$where); $this->delete('xyg_account',$where);
    }
    private function update(string $uid,string $rev,int $count,int $until): bool {
        $q=$this->db->getQueryBuilder(); $q->update('xyg_account');
        foreach (['failures'=>$count,'until_ts'=>$until,'revision'=>$this->revision()] as $k=>$v) {
            $q->set($k,$q->createNamedParameter($v));
        }
        $this->where($q,['subject'=>$this->subject($uid),'revision'=>$rev]);
        return $q->executeStatement()===1;
    }

    // Une seule écriture conditionnelle. Une ancienne confirmation ne peut pas
    // lever un nouveau verrou ni effacer des échecs en l'absence de verrou.
    public function unlockCurrentLock(string $uid, string $revision): bool {
        $q=$this->db->getQueryBuilder();
        $q->update('xyg_account');
        foreach (['failures'=>0, 'until_ts'=>0, 'revision'=>$this->revision()] as $k=>$v) {
            $q->set($k,$q->createNamedParameter($v));
        }
        $this->where($q,['subject'=>$this->subject($uid),'revision'=>$revision]);
        $q->andWhere($q->expr()->gt('until_ts',$q->createNamedParameter(time())));
        return $q->executeStatement()===1;
    }

    public function unlock(string $uid): bool {
        for ($i=0;$i<8;$i++) {
            $a=$this->account($uid);
            if (!$a) { return false; }
            if ($this->update($uid,$a['revision'],0,0)) { return true; }
        }
        return false;
    }
    public function verify(string $uid,string $wire): bool {
        $t=$this->consume($uid,'login',$wire);
        if (!$t) { return false; }
        for ($i=0;$i<8;$i++) {
            $a=$this->account($uid); $now=time();
            if (!$a || (int)$a['until_ts']>$now || !hash_equals($a['verifier'],$t['proof'])) {
                return false;
            }
            $ok=$t['digest']!==null && hash_equals($a['verifier'],$t['digest']);
            $old=(int)$a['until_ts']>0 ? 0 : (int)$a['failures'];
            $n=$ok?0:$old+1; $until=$n>=3?$now+3600:0;
            if ($this->update($uid,$a['revision'],$n,$until)) {
                $this->audit($ok?'login_ok':($until?'locked_1h':'login_failed'),$uid,!$ok);
                return $ok;
            }
        }
        $this->audit('concurrent_update_denied',$uid,true);
        return false;
    }
}
