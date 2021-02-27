<?php

use App\Generator\TreesGenerator;
use Migrations\AbstractSeed;

/**
 * Trees seed.
 */
class TreesSeed extends AbstractSeed
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
        $generator = new TreesGenerator();
        $data = $generator->generate(2000);

        $table = $this->table('trees');
        $table->insert($data)->save();
    }
}
