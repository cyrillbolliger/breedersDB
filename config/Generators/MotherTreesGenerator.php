<?php

namespace App\Generator;

use App\Model\Entity\Tree;
use DateInterval;
use DateTime;

/**
 * MotherTrees generator.
 */
class MotherTreesGenerator {

    public function generate( int $count ) {
        $treesTable     = \Cake\ORM\TableRegistry::getTableLocator()->get( 'Trees' );
        $trees          = $treesTable->find()
                                     ->contain(['Varieties', 'Varieties.Batches'])
                                     ->toArray();

        $faker   = \Faker\Factory::create();
        $start   = new DateTime( '-1 year' );
        $flowers = $faker->numberBetween( 20, 80 );
        $data    = [];
        for ( $i = 0; $i < $count; $i ++ ) {
            $crossing = $faker->unique()->regexify( '[A-Z]{1,2}[A-Za-z0-9]{3}[A-Z]{1}[A-Za-z0-9]{1,2}' );
            /** @var Tree $tree */
            $tree = $faker->randomElement($trees);

            $data[]   = [
                'code'                  => sprintf( '%04d_%s', $faker->unique()->numberBetween( 1, 1999 ), $crossing ),
                'planed'                => '0',
                'date_pollen_harvested' => $start->sub( new DateInterval( 'P1D' ) )->format( 'Y-m-d' ),
                'date_impregnated'      => $start->format( 'Y-m-d' ),
                'date_fruit_harvested'  => $start->add( new DateInterval( 'P5M' ) )->format( 'Y-m-d' ),
                'numb_portions'         => $faker->numberBetween( 10, 30 ),
                'numb_flowers'          => $flowers,
                'numb_fruits'           => (int) ( $flowers * 0.7 ),
                'numb_seeds'            => $flowers * 5,
                'note'                  => $faker->sentence(),
                'tree_id'               => $tree->id,
                'crossing_id'           => $tree->variety->batch->crossing_id,
                'deleted'               => null,
                'created'               => date( 'Y-m-d H:i:s' ),
                'modified'              => date( 'Y-m-d H:i:s' )
            ];
        }

        return $data;
    }
}
