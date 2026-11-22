<?php
declare(strict_types=1);
namespace OCA\TwoFactorXYGrid\Settings;
use OCA\TwoFactorXYGrid\Service\Store;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IURLGenerator;
use OCP\IUserSession;
use OCP\Settings\ISettings;

final class Personal implements ISettings {
    public function __construct(private Store $store,private IUserSession $users,
        private IURLGenerator $urls) {}
    public function getSection(): string { return 'security'; }
    public function getPriority(): int { return 65; }
    public function getForm(): TemplateResponse {
        $u=$this->users->getUser();
        $p=['active'=>$u && $this->store->account($u->getUID())!==null];
        foreach (['begin','confirm','disable'] as $op) {
            $p[$op]=$this->urls->linkToRoute('twofactor_xygrid.settings.'.$op);
        }
        return new TemplateResponse('twofactor_xygrid','personal',$p,'');
    }
}
