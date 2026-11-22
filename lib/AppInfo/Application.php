<?php
declare(strict_types=1);
namespace OCA\TwoFactorXYGrid\AppInfo;

use OCA\TwoFactorXYGrid\Listener\Deleted;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\User\Events\UserDeletedEvent;

final class Application extends App implements IBootstrap {
    public const ID = 'twofactor_xygrid';
    public function __construct() { parent::__construct(self::ID); }
    public function register(IRegistrationContext $context): void {
        $context->registerEventListener(UserDeletedEvent::class, Deleted::class);
    }
    public function boot(IBootContext $context): void {}
}
