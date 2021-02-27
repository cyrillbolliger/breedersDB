<?php

use App\Generator\MarkFormPropertiesGenerator;
use Migrations\AbstractSeed;

/**
 * MarkFormProperties seed.
 */
class MarkFormPropertiesSeed extends AbstractSeed {
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
    public function run() {
        $generator = new MarkFormPropertiesGenerator();
        $data = $generator->generate();

        $table = $this->table( 'mark_form_properties' );
        $table->insert( $data )->save();
    }
}
