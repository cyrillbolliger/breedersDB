<?php

namespace App\Test\TestCase\Controller\Shared;

use App\Model\Entity\MarkFormProperty;
use App\Model\Entity\MarkFormPropertyType;
use Cake\ORM\Query;

trait MarkFormPropertiesControllerTestTrait
{
    private function addEntity(): MarkFormProperty {
        $data   = $this->getNonExistingEntityData();
        $entity = $this->Table->newEntity( $data );

        $saved = $this->Table->saveOrFail( $entity );

        return $this->Table->get( $saved->id, [
            'contain' => self::CONTAINS,
        ] );
    }

    private function getNonExistingEntityData(): array {
        /** @var MarkFormPropertyType $propertyType */
        $propertyType = $this->getTable( 'MarkFormPropertyTypes' )
            ->find()
            ->firstOrFail();

        $data = [
            'name'                       => 'mark form property',
            'field_type'                 => 'BOOLEAN',
            'note'                       => 'this is a note',
            'mark_form_property_type_id' => $propertyType->id,
            'tree_property'              => true,
            'variety_property'           => true,
            'batch_property'             => false,
        ];

        $query = $this->getEntityQueryFromArray( $data );

        $this->deleteWithAssociatedData( $query );

        return $data;
    }

    private function deleteWithAssociatedData( Query $query ): void {
        $fieldsTable = $this->getTable( 'MarkFormFields' );
        $valuesTable = $this->getTable( 'MarkValues' );

        /** @var MarkFormProperty $property */
        foreach ( $query as $property ) {
            $fieldsTable->deleteManyOrFail( $property->mark_form_fields );
            $valuesTable->deleteManyOrFail( $property->mark_values );
        }

        $this->Table->deleteManyOrFail( $query );
    }

    private function getEntityQueryFromArray( array $data ): Query {
        return $this->Table->find()
            ->contain( self::CONTAINS )
            ->where( [ self::TABLE . '.name' => $data['name'] ] );
    }

    private function assertEntityExists( array $expected ): void {
        $query = $this->getEntityQueryFromArray( $expected );

        self::assertEquals( 1, $query->count() );

        /** @var MarkFormProperty $dbData */
        $dbData = $query->first();
        self::assertEquals( $dbData->name, $expected['name'] );
        self::assertEquals( $dbData->validation_rule, $expected['validation_rule'] );
        self::assertEquals( $dbData->field_type, $expected['field_type'] );
        self::assertEquals( $dbData->note, $expected['note'] );
        self::assertEquals( $dbData->mark_form_property_type_id, $expected['mark_form_property_type_id'] );
        self::assertEquals( $dbData->tree_property, $expected['tree_property'] );
        self::assertEquals( $dbData->variety_property, $expected['variety_property'] );
        self::assertEquals( $dbData->batch_property, $expected['batch_property'] );
    }
}
