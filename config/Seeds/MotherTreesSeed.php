<?php

use App\Generator\MotherTreesGenerator;
use Migrations\AbstractSeed;

/**
 * MotherTrees seed.
 */
class MotherTreesSeed extends AbstractSeed
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
        $generator = new MotherTreesGenerator();
        $data = $generator->generate(100);

        $table = $this->table('mother_trees');
        $table->insert($data)->save();
    }
}
