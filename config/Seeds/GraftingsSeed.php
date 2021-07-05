<?php

use App\Generator\GraftingsGenerator;
use Migrations\AbstractSeed;

/**
 * Graftings seed.
 */
class GraftingsSeed extends AbstractSeed
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
        $generator = new GraftingsGenerator();
        $data = $generator->generate(5);

        $table = $this->table('graftings');
        $table->insert($data)->save();
    }
}
