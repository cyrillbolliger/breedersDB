<?php

use App\Generator\CrossingsGenerator;
use Migrations\AbstractSeed;

/**
 * Crossings seed.
 */
class CrossingsSeed extends AbstractSeed
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
        $generator = new CrossingsGenerator();
        $data = $generator->generate(100);

        $table = $this->table('crossings');
        $table->insert($data)->save();
    }
}
