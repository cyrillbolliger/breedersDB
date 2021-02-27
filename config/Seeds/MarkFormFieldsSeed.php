<?php

use App\Generator\MarkFormFieldsGenerator;
use Migrations\AbstractSeed;

/**
 * MarkFormFields seed.
 */
class MarkFormFieldsSeed extends AbstractSeed
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
        $generator = new MarkFormFieldsGenerator();
        $data = $generator->generate();

        $table = $this->table('mark_form_fields');
        $table->insert($data)->save();
    }
}
