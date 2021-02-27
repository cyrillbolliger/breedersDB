<?php

namespace App\Generator;

/**
 * Varieties generator.
 */
class VarietiesGenerator {

    public function generate( int $count ) {
        $faker = \Faker\Factory::create();
        $data  = [];
        // official varieties
        for ( $i = 0; $i < ceil( $count * 0.1 ); $i ++ ) {
            $word   = $faker->unique()->word();
            $data[] = [
                'code'          => strtolower( $word ),
                'official_name' => ucfirst( $word ),
                'acronym'       => ucfirst( substr( $word, 0, 3 ) ),
                'plant_breeder' => $faker->name,
                'registration'  => null,
                'description'   => $faker->sentence(),
                'batch_id'      => '1',
                'deleted'       => null,
                'created'       => date( 'Y-m-d H:i:s' ),
                'modified'      => date( 'Y-m-d H:i:s' ),
                'convar'        => 'SORTE.000.' . $word,
            ];
        }

        // breeder varieties
        $batchesTable = \Cake\ORM\TableRegistry::getTableLocator()->get( 'Batches' );
        $batches      = $batchesTable->find()->toArray();

        for ( $i = 0; $i < ceil( $count * 0.9 ); $i ++ ) {
            $batch = $faker->randomElement( $batches );
            $code = sprintf( '%03d', $faker->numberBetween( 1, 99 ) );
            $data[]  = [
                'code'          => $code,
                'official_name' => '',
                'acronym'       => '',
                'plant_breeder' => '',
                'registration'  => null,
                'description'   => $faker->sentence(),
                'batch_id'      => $batch->id,
                'deleted'       => null,
                'created'       => date( 'Y-m-d H:i:s' ),
                'modified'      => date( 'Y-m-d H:i:s' ),
                'convar'        => $batch->crossing_batch.'.'.$code,
            ];
        }

        return $data;
    }
}
