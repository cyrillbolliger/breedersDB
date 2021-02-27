<?php

use App\Generator\MarksGenerator;
use Migrations\AbstractSeed;

/**
 * Marks seed.
 */
class MarksSeed extends AbstractSeed {
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
        $generator = new MarksGenerator();
        $data = $generator->generate();

        $table = $this->table( 'marks' );
        $table->insert( $data )->save();
    }
}
