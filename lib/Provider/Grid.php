<?php
declare(strict_types=1);
namespace OCA\TwoFactorXYGrid\Provider;

use OCA\TwoFactorXYGrid\Service\Store;
use OCP\Authentication\TwoFactorAuth\IProvider;
use OCP\IUser;
use OCP\Template;

final class Grid implements IProvider {
    public function __construct(private Store $store) {}
    public function getId(): string { return 'xygrid'; }
    public function getDisplayName(): string { return 'Code personnel sur grille'; }
    public function getDescription(): string { return 'Sélectionnez les six chiffres de votre code.'; }
    public function isTwoFactorAuthEnabledForUser(IUser $user): bool {
        return $this->store->account($user->getUID())!==null;
    }
    public function getTemplate(IUser $user): Template {
        $uid=$user->getUID(); $a=$this->store->account($uid);
        $until=(int)($a['until_ts']??0);
        $t=new Template('twofactor_xygrid','challenge');
        $t->assign('minutes',max(0,(int)ceil(($until-time())/60)));
        $t->assign('remaining',$until>0?3:3-(int)($a['failures']??0));
        $t->assign('board',$a && $until<=time()?$this->store->issue($uid,'login'):[]);
        return $t;
    }
    public function verifyChallenge(IUser $user,$challenge): bool {
        return is_string($challenge) && $this->store->verify($user->getUID(),$challenge);
    }
}
