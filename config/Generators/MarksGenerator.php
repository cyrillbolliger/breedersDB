<?php
namespace App\Generator;


/**
 * Marks generator.
 */
class MarksGenerator {

    public function generate() {
        $treesTable = \Cake\ORM\TableRegistry::getTableLocator()->get( 'Trees' );
        $data = [];

        $faker = \Faker\Factory::create();

        foreach ( $treesTable->find() as $tree ) {
            for ($i = 0; $i < $faker->numberBetween(0,5); $i++) {
                $date = $faker->dateTimeBetween('-5 years', 'now');
                $data[] = [
                    'date'         => $date->format('Y-m-d'),
                    'author'       => $faker->randomElement(['Max', 'Maria']),
                    'mark_form_id' => $faker->numberBetween(1,3),
                    'tree_id'      => $tree->id,
                    'variety_id'   => null,
                    'batch_id'     => null,
                    'created'      => $date->format('Y-m-d H:i:s'),
                    'modified'     => $date->format('Y-m-d H:i:s'),
                ];
            }
        }

        return $data;
    }
}
