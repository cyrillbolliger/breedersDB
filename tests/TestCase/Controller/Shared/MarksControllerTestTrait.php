<?php

namespace App\Test\TestCase\Controller\Shared;

use App\Model\Entity\Mark;
use App\Model\Entity\MarkForm;
use App\Model\Entity\Tree;
use Cake\ORM\Query;

trait MarksControllerTestTrait
{
    private function addEntity( $withMarkValues = false ): Mark {
        $data   = $this->getNonExistingEntityData();
        $entity = $this->Table->newEntity( $data );

        if ( $withMarkValues ) {
            $ValuesTable = $this->getTable( 'MarkValues' );
            $data        = $this->appendMarkValues( $data );
            foreach ( $data['mark_form_fields']['mark_form_properties'] as $property_id => $value ) {
                $mark_values[] = $ValuesTable->newEntity( [
                    'value'                 => $value['mark_values']['value'],
                    'exceptional_mark'      => false,
                    'mark_form_property_id' => $property_id
                ] );
            }
            $entity->mark_values = $mark_values ?? [];
        }

        $saved = $this->Table->saveOrFail( $entity );

        return $this->Table->get( $saved->id, [
            'contain' => self::CONTAINS,
        ] );
    }

    private function getNonExistingEntityData(): array {
        /** @var MarkForm $form */
        $form = $this->getTable( 'MarkForms' )
                     ->find()
                     ->contain( [ 'MarkFormFields', 'MarkFormFields.MarkFormProperties' ] )
                     ->matching( 'MarkFormFields', function ( $q ) {
                         return $q->where( [ 'MarkFormFields.mark_form_id = MarkForms.id' ] );
                     } )
                     ->firstOrFail();

        /** @var Tree $tree */
        $tree = $this->getTable( 'Trees' )
                     ->find()
                     ->firstOrFail();

        $faker = \Faker\Factory::create();

        $data = [
            'date'         => '11.12.2020',
            'author'       => $faker->uuid, // misuse the author as unique identifier
            'mark_form_id' => $form->id,
            'tree_id'      => $tree->id
        ];

        $query = $this->getEntityQueryFromArray( $data );

        $this->deleteWithAssociatedData( $query );

        return $data;
    }

    private function appendMarkValues( array $data ): array {
        /** @var MarkForm $form */
        $form = $this->getTable( 'MarkForms' )
                     ->get(
                         $data['mark_form_id'],
                         [
                             'contain' => [
                                 'MarkFormFields',
                                 'MarkFormFields.MarkFormProperties'
                             ]
                         ] );

        $faker = \Faker\Factory::create();

        foreach ( $form->mark_form_fields as $field ) {
            $property = $field->mark_form_property;

            switch ( $property->field_type ) {
                case 'FLOAT':
                case 'INTEGER':
                    $value = $faker->numberBetween( $property->validation_rule['min'], $property->validation_rule['max'] );
                    break;
                case 'BOOLEAN':
                    $value = $faker->boolean;
                    break;
                case 'DATE':
                    $value = $faker->date( 'd.m.Y' );
                    break;
                default:
                    $value = $faker->sentence;
            }

            $data['mark_form_fields']['mark_form_properties'][ $property->id ]['mark_values']['value'] = $value;
        }

        return $data;
}

    private function deleteWithAssociatedData( Query $query ): void {
        $markValuesTable = $this->getTable( 'MarkValues' );

        /** @var Mark $mark */
        foreach ( $query->all() as $mark ) {
            $markValuesTable->deleteManyOrFail( $mark->mark_values );
        }

        $this->Table->deleteManyOrFail( $query );
    }

    private function getEntityQueryFromArray( array $data ): Query {
        return $this->Table->find()
                           ->contain( self::CONTAINS )
                           ->where( [ self::TABLE . '.author' => $data['author'] ] );
    }

    private function assertEntityExists( array $expected ): void {
        $query = $this->getEntityQueryFromArray( $expected );

        self::assertEquals( 1, $query->count() );

        /** @var Mark $dbData */
        $dbData = $query->first();
        self::assertEquals( $dbData->date, $expected['date'] );
        self::assertEquals( $dbData->author, $expected['author'] );
        self::assertEquals( $dbData->mark_form_id, $expected['mark_form_id'] );

        foreach ( [ 'tree_id', 'variety_id', 'batch_id' ] as $type ) {
            if ( array_key_exists( $type, $expected ) ) {
                self::assertEquals( $expected[ $type ], $dbData->$type );
            } else {
                self::assertEmpty( $dbData->$type );
            }
        }

        if ( array_key_exists( 'mark_form_properties', $expected ) ) {
            foreach ( $expected['mark_form_properties'] as $property_id => $property ) {
                $expectedValue = $property['mark_values']['value'];
                $storedValue   = null;
                foreach ( $dbData->mark_values as $markValue ) {
                    if ( $markValue->id === $property_id ) {
                        $storedValue = $markValue->value;
                        break;
                    }
                }

                self::assertEquals( $expectedValue, $storedValue );
            }
        }
    }
}
