<?php
namespace App\Generator;
use Cake\Auth\DefaultPasswordHasher;


/**
 * Users generator.
 */
class UsersGenerator {

    public function generate(int $count) {
        $faker  = \Faker\Factory::create();
        $hasher = new DefaultPasswordHasher();
        $data   = [];
        for ( $i = 0; $i < $count; $i ++ ) {
            $data[] = [
                'email'     => $faker->email,
                'password'  => $hasher->hash( random_bytes( 64 ) ),
                'level'     => '0',
                'time_zone' => 'Europe/Brussels',
                'created'   => date( 'Y-m-d H:i:s' ),
                'modified'  => date( 'Y-m-d H:i:s' ),
            ];
        }

        return $data;
    }
}
