<?php

use Cake\Auth\DefaultPasswordHasher;
use Migrations\AbstractSeed;

/**
 * Users seed.
 */
class UsersSeed extends AbstractSeed {
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
        $faker  = Faker\Factory::create();
        $hasher = new DefaultPasswordHasher();
        $data   = [];
        for ( $i = 0; $i < 2; $i ++ ) {
            $data[] = [
                'email'     => $faker->email,
                'password'  => $hasher->hash( random_bytes( 64 ) ),
                'level'     => '0',
                'time_zone' => 'Europe/Brussels',
                'created'   => date( 'Y-m-d H:i:s' ),
                'modified'  => date( 'Y-m-d H:i:s' ),
            ];
        }

        $table = $this->table( 'users' );
        $table->insert( $data )->save();
    }
}
