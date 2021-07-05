<?php

use App\Generator\RootstocksGenerator;
use Migrations\AbstractSeed;

/**
 * Rootstocks seed.
 */
class RootstocksSeed extends AbstractSeed
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
        $generator = new RootstocksGenerator();
        $data = $generator->generate();

        $table = $this->table('rootstocks');
        $table->insert($data)->save();
    }
}
