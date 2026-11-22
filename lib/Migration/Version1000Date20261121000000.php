<?php
declare(strict_types=1);
namespace OCA\TwoFactorXYGrid\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

final class Version1000Date20261121000000 extends SimpleMigrationStep {
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        $s=$schemaClosure();
        if (!$s->hasTable('xyg_account')) {
            $a=$s->createTable('xyg_account');
            foreach (['subject'=>64,'verifier'=>64,'revision'=>32] as $n=>$len) {
                $a->addColumn($n, Types::STRING, ['length'=>$len,'notnull'=>true]);
            }
            $a->addColumn('failures',Types::INTEGER,['default'=>0,'notnull'=>true]);
            $a->addColumn('until_ts',Types::BIGINT,['default'=>0,'notnull'=>true]);
            $a->setPrimaryKey(['subject']);
        }
        if (!$s->hasTable('xyg_ticket')) {
            $t=$s->createTable('xyg_ticket');
            foreach (['ticket'=>64,'subject'=>64,'binding'=>64,'purpose'=>8,
                      'layout'=>10,'proof'=>64] as $n=>$len) {
                $t->addColumn($n,Types::STRING,['length'=>$len,'notnull'=>true]);
            }
            $t->addColumn('expires_ts',Types::BIGINT,['notnull'=>true]);
            $t->setPrimaryKey(['ticket']);
            $t->addIndex(['expires_ts'],'xyg_exp_idx');
            $t->addIndex(['subject','purpose'],'xyg_sub_idx');
        }
        return $s;
    }
}
