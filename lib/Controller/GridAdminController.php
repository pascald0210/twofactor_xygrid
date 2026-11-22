<?php
declare(strict_types=1);
namespace OCA\TwoFactorXYGrid\Controller;

use OCA\TwoFactorXYGrid\Service\Store;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\PasswordConfirmationRequired;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUserManager;
use OCP\IUserSession;
use Psr\Log\LoggerInterface;

final class GridAdminController extends Controller {
    public function __construct(IRequest $request, private Store $store,
        private IUserSession $session, private IUserManager $users,
        private IGroupManager $groups, private LoggerInterface $logger) {
        parent::__construct('twofactor_xygrid', $request);
    }
    private function reply(array $data, int $status=200): JSONResponse {
        $r = new JSONResponse($data, $status);
        $r->addHeader('Cache-Control', 'no-store, private');
        return $r;
    }
    private function actor(): ?string {
        $user = $this->session->getUser();
        return $user !== null && $this->groups->isAdmin($user->getUID())
            ? $user->getUID() : null;
    }
    private function target(string $uid): ?string {
        if ($uid === '' || strlen($uid) > 1024 || preg_match('/[\x00-\x1f\x7f]/', $uid)) {
            return null;
        }
        return $this->users->get($uid)?->getUID();
    }
    private function audit(string $actor, string $uid, string $result): void {
        $this->logger->warning('xygrid: admin_unlock actor={actor} account={account} result={result}', [
            'app'=>'twofactor_xygrid', 'event'=>'admin_unlock', 'actor'=>$actor,
            'account'=>$uid, 'result'=>$result,
        ]);
    }
    // Admin-only; protection CSRF native conservée sur les deux routes POST.
    public function lookup(string $uid=''): JSONResponse {
        if ($this->actor() === null) { return $this->reply([], 403); }
        $id = $this->target($uid);
        if ($id === null) { return $this->reply(['state'=>'unknown']); }
        $a = $this->store->account($id);
        if (!$a) { return $this->reply(['state'=>'inactive', 'uid'=>$id]); }
        $seconds = (int)$a['until_ts'] - time();
        if ($seconds <= 0) { return $this->reply(['state'=>'open', 'uid'=>$id]); }
        // Cette révision est un marqueur de concurrence, pas un identifiant de connexion.
        return $this->reply(['state'=>'locked','uid'=>$id,'revision'=>$a['revision'],
            'minutes'=>(int)ceil($seconds/60)]);
    }
    #[PasswordConfirmationRequired]
    public function unlock(string $uid='', string $revision=''): JSONResponse {
        $actor = $this->actor();
        if ($actor === null) { return $this->reply([], 403); }
        $id = $this->target($uid);
        if ($id === null || !preg_match('/\A[a-f0-9]{32}\z/', $revision)) {
            $this->audit($actor, $id ?? '[invalid target]', 'invalid_request');
            return $this->reply(['state'=>'changed'], 409);
        }
        try {
            $done = $this->store->unlockCurrentLock($id, $revision);
        } catch (\Throwable $e) {
            $this->audit($actor, $id, 'database_error');
            throw $e;
        }
        $this->audit($actor, $id, $done ? 'unlocked' : 'not_changed');
        return $this->reply(['state'=>$done ? 'unlocked' : 'changed'], $done ? 200 : 409);
    }
}
