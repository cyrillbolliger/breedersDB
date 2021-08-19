<?php

namespace App\Test\TestCase\Controller\Shared;

use App\Model\Entity\MarkForm;
use Cake\ORM\Query;

trait MarkFormsControllerTestTrait
{
    private function addEntity(): MarkForm {
        $data = $this->getNonExistingEntityData();

        $data = $this->convertEntityDataArrayToInsertableDataArray( $data );

        $entity = $this->Table->newEntity( $data );

        $saved = $this->Table->saveOrFail( $entity );

        return $this->Table->get( $saved->id, [
            'contain' => self::CONTAINS,
        ] );
    }

    private function getNonExistingEntityData(): array {
        $properties = $this->getTable( 'MarkFormProperties' )
                           ->find()
                           ->all();

        $data = [
            'name'             => 'myForm',
            'description'      => 'what a wonderfull form',
            'mark_form_fields' => [
                'mark_form_properties' => [
                    $properties->first()->id => [
                        'mark_values' => [
                            'value' => ''
                        ]
                    ],
                    $properties->last()->id  => [
                        'mark_values' => [
                            'value' => ''
                        ]
                    ]
                ]
            ]
        ];

        $query = $this->getEntityQueryFromArray( $data );

        $this->deleteWithAssociatedData( $query );

        return $data;
    }

    private function deleteWithAssociatedData( Query $query ): void {
        $fieldsTable = $this->getTable( 'MarkFormFields' );

        /** @var MarkForm $form */
        foreach ( $query as $form ) {
            $fieldsTable->deleteManyOrFail( $form->mark_form_fields );
        }

        $this->Table->deleteManyOrFail( $query );
    }

    private function convertEntityDataArrayToInsertableDataArray( $data ) {
        $propertyIds = $this->extractPropertyIdsFromEntityDataArray( $data );

        foreach ( $propertyIds as $i => $propertyId ) {
            $data['mark_form_fields'][ $i ] = [
                'mark_form_property_id' => $propertyId,
                'priority'              => $i,
            ];
        }

        unset( $data['mark_form_fields']['mark_form_properties'] );

        return $data;
    }

    private function extractPropertyIdsFromEntityDataArray( $data ): array {
        return array_keys( $data['mark_form_fields']['mark_form_properties'] );
    }

    private function getEntityQueryFromArray( array $data ): Query {
        return $this->Table->find()
                           ->contain( self::CONTAINS )
                           ->where( [ self::TABLE . '.name' => $data['name'] ] );
    }

    private function assertEntityExists( array $expected ): void {
        $expected = $this->convertEntityDataArrayToInsertableDataArray( $expected );
        $query    = $this->getEntityQueryFromArray( $expected );

        self::assertEquals( 1, $query->count() );

        /** @var MarkForm $dbData */
        $dbData = $query->first();
        self::assertEquals( $dbData->name, $expected['name'] );
        self::assertEquals( $dbData->description, $expected['description'] );

        foreach ( $dbData['mark_form_fields'] as $actualField ) {
            $testableFieldData = $actualField->extract( [ 'mark_form_property_id', 'priority' ] );
            self::assertContains( $testableFieldData, $expected['mark_form_fields'] );
        }

        self::assertSameSize( $expected['mark_form_fields'], $dbData['mark_form_fields'] );
    }
}
