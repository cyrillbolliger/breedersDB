<?php

use App\Generator\BatchesGenerator;
use Migrations\AbstractSeed;

/**
 * Batches seed.
 */
class BatchesSeed extends AbstractSeed
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
        $generator = new BatchesGenerator();
        $data = $generator->generate(100);

        $table = $this->table('batches');
        $table->insert($data)->save();
    }
}
