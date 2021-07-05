<?php

use App\Generator\RowsGenerator;
use Migrations\AbstractSeed;

/**
 * Rows seed.
 */
class RowsSeed extends AbstractSeed
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
        $generator = new RowsGenerator();
        $data = $generator->generate(15);

        $table = $this->table('rows');
        $table->insert($data)->save();
    }
}
