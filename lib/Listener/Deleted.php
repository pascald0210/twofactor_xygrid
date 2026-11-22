<?php
declare(strict_types=1);
namespace OCA\TwoFactorXYGrid\Listener;
use OCA\TwoFactorXYGrid\Service\Store;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\User\Events\UserDeletedEvent;

final class Deleted implements IEventListener {
    public function __construct(private Store $store) {}
    public function handle(Event $event): void {
        if ($event instanceof UserDeletedEvent) {
            $this->store->erase($event->getUser()->getUID());
        }
    }
}
