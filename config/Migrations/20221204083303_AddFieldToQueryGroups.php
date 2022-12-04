<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddFieldToQueryGroups extends AbstractMigration
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
        $table = $this->table('query_groups');
        $table->addColumn('version', 'string', [
            'default' => null,
            'limit' => 45,
            'null' => true,
            'after' => 'code',
        ]);
        $table->update();
    }
}
