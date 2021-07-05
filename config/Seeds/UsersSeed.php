<?php

use App\Generator\UsersGenerator;
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
        $generator = new UsersGenerator();
        $data = $generator->generate(3);

        $table = $this->table( 'users' );
        $table->insert( $data )->save();
    }
}
