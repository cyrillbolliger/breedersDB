<?php
use Migrations\AbstractSeed;

/**
 * QueryGroups seed.
 */
class QueryGroupsSeed extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeds is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'id' => '1',
                'code' => 'Bonituren',
                'deleted' => NULL,
                'created' => '2020-11-29 22:44:02',
                'modified' => '2020-11-29 22:44:02',
            ],
            [
                'id' => '2',
                'code' => 'Pflanzen',
                'deleted' => NULL,
                'created' => '2020-11-29 22:57:15',
                'modified' => '2020-11-29 23:13:57',
            ],
        ];

        $table = $this->table('query_groups');
        $table->insert($data)->save();
    }
}
