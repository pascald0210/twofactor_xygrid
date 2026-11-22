<?php
declare(strict_types=1);
namespace OCA\TwoFactorXYGrid\Controller;

use OCA\TwoFactorXYGrid\Service\Lifecycle;
use OCA\TwoFactorXYGrid\Service\Store;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\PasswordConfirmationRequired;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;

final class SettingsController extends Controller {
    public function __construct(IRequest $request,private IUserSession $users,
        private Store $store,private Lifecycle $life) {
        parent::__construct('twofactor_xygrid',$request);
    }
    private function user(): IUser {
        return $this->users->getUser() ?? throw new \RuntimeException('Session requise.');
    }
    private function reply(array $data,int $status=200): JSONResponse {
        $r=new JSONResponse($data,$status);
        $r->addHeader('Cache-Control','no-store, private');
        return $r;
    }
    #[NoAdminRequired]
    #[PasswordConfirmationRequired]
    public function begin(): JSONResponse {
        $uid=$this->user()->getUID();
        if ($this->store->account($uid)!==null) {
            return $this->reply(['error'=>'La grille est déjà active.'],409);
        }
        return $this->reply($this->store->issue($uid,'enrol'));
    }
    #[NoAdminRequired]
    #[PasswordConfirmationRequired]
    public function confirm(string $challenge=''): JSONResponse {
        $u=$this->user(); $uid=$u->getUID();
        if ($this->store->account($uid)!==null) {
            return $this->reply(['error'=>'La grille est déjà active.'],409);
        }
        $proof=$this->store->enrolProof($uid,$challenge);
        if ($proof===null) {
            return $this->reply(['error'=>'Confirmation incorrecte ou expirée. Recommencez l’activation.'],400);
        }
        $this->life->enable($u,$proof);
        return $this->reply(['active'=>true]);
    }
    #[NoAdminRequired]
    #[PasswordConfirmationRequired]
    public function disable(): JSONResponse {
        $this->life->revoke($this->user(),'disabled_by_user');
        return $this->reply(['active'=>false]);
    }
}
