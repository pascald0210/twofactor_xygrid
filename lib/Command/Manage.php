<?php
declare(strict_types=1);
namespace OCA\TwoFactorXYGrid\Command;

use OCA\TwoFactorXYGrid\Service\Lifecycle;
use OCA\TwoFactorXYGrid\Service\Store;
use OCP\IUserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class Manage extends Command {
    public function __construct(private Store $store,private Lifecycle $life,private IUserManager $users) {
        parent::__construct('xygrid:manage');
    }
    protected function configure(): void {
        $this->setDescription('Consulter, déverrouiller ou révoquer une grille ; purger les tickets expirés.')
            ->addArgument('action',InputArgument::REQUIRED,'status | unlock | revoke | purge')
            ->addArgument('uid',InputArgument::OPTIONAL,'Identifiant interne du compte')
            ->addOption('yes',null,InputOption::VALUE_NONE,'Confirmer la modification');
    }
    protected function execute(InputInterface $in,OutputInterface $out): int {
        $op=$in->getArgument('action'); $uid=$in->getArgument('uid');
        if (!in_array($op,['status','unlock','revoke','purge'],true)) {
            $out->writeln('Action inconnue.'); return 2;
        }
        if ($op!=='status' && !$in->getOption('yes')) {
            $out->writeln('Ajoutez --yes pour confirmer.'); return 2;
        }
        if ($op==='purge') {
            if ($uid!==null) { $out->writeln('purge ne prend pas d’UID.'); return 2; }
            $out->writeln('Tickets expirés supprimés : '.$this->store->purgeExpired()); return 0;
        }
        if ($uid===null || $uid==='') { $out->writeln('UID requis.'); return 2; }
        $u=$this->users->get($uid);
        if (!$u) { $out->writeln('Compte introuvable.'); return 1; }
        $uid=$u->getUID(); $a=$this->store->account($uid);
        if ($op==='status') {
            $until=(int)($a['until_ts']??0); $locked=$until>time();
            $out->writeln(json_encode(['active'=>$a!==null,'locked'=>$locked,
                'failed_attempts'=>$until && !$locked?0:(int)($a['failures']??0),
                'locked_until_utc'=>$locked?gmdate('c',$until):null],JSON_THROW_ON_ERROR));
            return 0;
        }
        if ($op==='unlock') {
            if (!$this->store->unlock($uid)) { $out->writeln('Grille inactive ou occupée.'); return 1; }
            $this->store->audit('occ_unlock',$uid,true);
        } else {
            $this->life->revoke($u,'occ_revoke');
        }
        $out->writeln('Opération effectuée.'); return 0;
    }
}
