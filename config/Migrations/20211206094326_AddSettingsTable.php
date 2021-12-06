<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddSettingsTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $this->table( 'settings' )
            ->addColumn( 'setting_key', 'string', [
                'limit'   => 45,
                'null'    => false,
            ] )
            ->addColumn( 'setting_value', 'string', [
                'default' => null,
                'limit'   => 255,
                'null'    => true,
            ] )
            ->addIndex(
                [
                    'setting_key',
                ]
            )
            ->create();
    }
}
