<?php

use App\Generator\QueryGroupsGenerator;
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
        $generator = new QueryGroupsGenerator();
        $data = $generator->generate();

        $table = $this->table('query_groups');
        $table->insert($data)->save();
    }
}
