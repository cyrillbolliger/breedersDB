<?php

declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateTableUsersExperimentSites extends AbstractMigration
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
        $this->table('users_experiment_sites', ['signed' => true])
            ->addColumn('user_id', 'integer', ['signed' => true])
            ->addColumn('experiment_site_id', 'integer', ['signed' => true])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey(
                'experiment_site_id',
                'experiment_sites',
                'id',
                ['delete' => 'CASCADE', 'update' => 'CASCADE']
            )
            ->addIndex(['user_id', 'experiment_site_id'], ['unique' => true])
            ->addIndex(['experiment_site_id'])
            ->addIndex(['user_id'])
            ->create();
    }
}
