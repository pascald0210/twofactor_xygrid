<?php
declare(strict_types=1);
namespace OCA\TwoFactorXYGrid\Service;

use OCA\TwoFactorXYGrid\Provider\Grid;
use OCP\Authentication\TwoFactorAuth\IRegistry;
use OCP\IDBConnection;
use OCP\IUser;

final class Lifecycle {
    public function __construct(private Store $store,private Grid $grid,
        private IRegistry $registry,private IDBConnection $db) {}
    public function enable(IUser $u,string $proof): void {
        $this->db->beginTransaction();
        try {
            $this->store->create($u->getUID(),$proof);
            $this->registry->enableProviderFor($this->grid,$u);
            $this->db->commit();
        } catch (\Throwable $e) { $this->db->rollBack(); throw $e; }
        $this->store->audit('enabled',$u->getUID());
    }
    public function revoke(IUser $u,string $event): void {
        $this->db->beginTransaction();
        try {
            $this->store->erase($u->getUID());
            $this->registry->disableProviderFor($this->grid,$u);
            $this->db->commit();
        } catch (\Throwable $e) { $this->db->rollBack(); throw $e; }
        $this->store->audit($event,$u->getUID(),true);
    }
}
