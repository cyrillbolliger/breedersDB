<?php

use Migrations\AbstractSeed;

/**
 * Marks seed.
 */
class MarksSeed extends AbstractSeed {
    private Faker\Generator $faker;

    public function init() {
        $this->faker = Faker\Factory::create();
    }

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
        $treesTable = \Cake\ORM\TableRegistry::getTableLocator()->get( 'Trees' );
        $data = [];

        foreach ( $treesTable->find() as $tree ) {
            for ($i = 0; $i < $this->faker->numberBetween(0,5); $i++) {
                $date = $this->faker->dateTimeBetween('-5 years', 'now');
                $data[] = [
                    'date'         => $date->format('Y-m-d'),
                    'author'       => $this->faker->randomElement(['Max', 'Maria']),
                    'mark_form_id' => $this->faker->numberBetween(1,3),
                    'tree_id'      => $tree->id,
                    'variety_id'   => null,
                    'batch_id'     => null,
                    'created'      => $date->format('Y-m-d H:i:s'),
                    'modified'     => $date->format('Y-m-d H:i:s'),
                ];
            }
        }

        $table = $this->table( 'marks' );
        $table->insert( $data )->save();
    }
}
