<?php

declare(strict_types=1);

use Migrations\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class IncreaseMaxQueryLen extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('queries');
        $table->changeColumn('my_query', 'text', [
            'default' => null,
            'limit' => MysqlAdapter::TEXT_MEDIUM,
            'null' => true,
        ]);
        $table->update();
    }
}
