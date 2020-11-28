<?php

use Migrations\AbstractSeed;

/**
 * MarkValues seed.
 */
class MarkValuesSeed extends AbstractSeed {
    private Faker\Generator $faker;
    private \App\Model\Entity\MarkFormProperty $property;
    private \App\Model\Entity\Mark $mark;

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
        $marksTable      = \Cake\ORM\TableRegistry::getTableLocator()->get( 'Marks' );
        $propertiesTable = \Cake\ORM\TableRegistry::getTableLocator()->get( 'MarkFormProperties' );
        $table           = $this->table( 'mark_values' );

        foreach ( $marksTable->find() as $mark ) {
            $this->mark = $mark;
            $data            = [];

            foreach ( $propertiesTable->find() as $property ) {
                $this->property = $property;
                for ( $i = 0; $i < $this->faker->numberBetween( 0, 1 ); $i ++ ) {
                    switch ( $property->field_type ) {
                        case 'INTEGER':
                            $data[] = $this->integer();
                            break;
                        case 'FLOAT':
                            $data[] = $this->float();
                            break;
                        case 'BOOLEAN':
                            $data[] = $this->boolean();
                            break;
                        case 'VARCHAR':
                            $data[] = $this->varchar();
                            break;
                        case 'DATE':
                            $data[] = $this->date();
                            break;
                    }
                }
            }

            // insert inside the loop to prevent exceeding max_allowed_packet
            $table->insert( $data )->save();
        }
    }

    private function integer() {
        $validation = $this->property->validation_rule;
        $min        = (int) $validation['min'];
        $max        = (int) $validation['max'];

        return $this->generateData( $this->faker->numberBetween( $min, $max ) );
    }

    private function float() {
        $validation = $this->property->validation_rule;
        $min        = (float) $validation['min'];
        $max        = (float) $validation['max'];

        return $this->generateData( $this->faker->randomFloat( 1, $min, $max ) );
    }


    private function boolean() {
        return $this->generateData( $this->faker->numberBetween( 0, 1 ) );
    }

    private function varchar() {
        return $this->generateData( $this->faker->sentence );
    }

    private function date() {
        return $this->generateData( $this->faker->dateTimeThisYear->format( 'Y-m-d' ) );
    }

    private function generateData( $value ) {
        $date = $this->faker->dateTimeThisYear->format( 'Y-m-d H:i:s' );

        return [
            'value'                 => $value,
            'exceptional_mark'      => (int) floor( $this->faker->randomFloat( 1, 0, 1.1 ) ),
            'mark_form_property_id' => $this->property->id,
            'mark_id'               => $this->mark->id,
            'created'               => $date,
            'modified'              => $date,
        ];
    }
}
