<?php

use App\Generator\VarietiesGenerator;
use Migrations\AbstractSeed;

/**
 * Varieties seed.
 */
class VarietiesSeed extends AbstractSeed
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
        $generator = new VarietiesGenerator();
        $data = $generator->generate(1000);

        $table = $this->table('varieties');
        $table->insert($data)->save();
    }
}
