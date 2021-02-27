<?php

use App\Generator\MarkValuesGenerator;
use Migrations\AbstractSeed;

/**
 * MarkValues seed.
 */
class MarkValuesSeed extends AbstractSeed {

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
        $generator = new MarkValuesGenerator();
        $data      = $generator->generate();

        $table = $this->table( 'mark_values' );
        $this->saveInParts( $table, $data );
    }

    /**
     * Save data part by part to prevent exceeding max_allowed_packet
     *
     * @param $table
     * @param $data
     */
    private function saveInParts( $table, $data ) {
        $limit = 1000;
        $rows  = count( $data );
        for ( $i = 0; $i < $rows; $i += $limit ) {
            $part = array_slice( $data, $i, $limit );
            $table->insert( $part )->save();
        }
    }
}
