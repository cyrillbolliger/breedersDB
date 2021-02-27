<?php

use App\Generator\MarkFormPropertyTypesGenerator;
use Migrations\AbstractSeed;

/**
 * MarkFormPropertyTypes seed.
 */
class MarkFormPropertyTypesSeed extends AbstractSeed
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
        $generator = new MarkFormPropertyTypesGenerator();
        $data = $generator->generate();

        $table = $this->table('mark_form_property_types');
        $table->insert($data)->save();
    }
}
