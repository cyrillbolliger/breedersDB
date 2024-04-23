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
                'password'  => $hasher->hash( bin2hex( random_bytes( 64 ) ) ),
                'level'     => '0',
                'time_zone' => 'Europe/Brussels',
            ];
        }

        return $data;
    }
}
