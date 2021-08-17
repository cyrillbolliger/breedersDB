<?php
namespace App\Generator;

/**
 * MarkValues generator.
 */
class MarkValuesGenerator {
    private \Faker\Generator $faker;
    private \App\Model\Entity\MarkFormProperty $property;
    private \App\Model\Entity\Mark $mark;


    public function generate() {
        $this->faker = \Faker\Factory::create();

        $marksTable      = \Cake\ORM\TableRegistry::getTableLocator()->get( 'Marks' );
        $propertiesTable = \Cake\ORM\TableRegistry::getTableLocator()->get( 'MarkFormProperties' );

        $data = [];
        foreach ( $marksTable->find() as $mark ) {
            $this->mark = $mark;

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
        }

        return $data;
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
        return $this->generateData( $this->faker->dateTimeThisYear->format( 'd.m.Y' ) );
    }

    private function generateData( $value ) {
        return [
            'value'                 => $value,
            'exceptional_mark'      => (int) floor( $this->faker->randomFloat( 1, 0, 1.1 ) ),
            'mark_form_property_id' => $this->property->id,
            'mark_id'               => $this->mark->id
        ];
    }
}
