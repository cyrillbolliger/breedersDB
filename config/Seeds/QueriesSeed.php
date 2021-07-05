<?php

use App\Generator\QueriesGenerator;
use Migrations\AbstractSeed;

/**
 * Queries seed.
 */
class QueriesSeed extends AbstractSeed
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
        $generator = new QueriesGenerator();
        $data = $generator->generate();

        $table = $this->table('queries');
        $table->insert($data)->save();
    }
}
