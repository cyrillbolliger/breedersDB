<?php

use App\Generator\MarkFormsGenerator;
use Migrations\AbstractSeed;

/**
 * MarkForms seed.
 */
class MarkFormsSeed extends AbstractSeed
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
        $generator = new MarkFormsGenerator();
        $data = $generator->generate();

        $table = $this->table('mark_forms');
        $table->insert($data)->save();
    }
}
