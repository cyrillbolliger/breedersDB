<?php

declare(strict_types=1);

namespace App\Test\TestCase\Controller\Shared;

use App\Model\Entity\QueryGroup;
use Cake\ORM\Query;

trait QueryGroupsControllerTestTrait
{
    private function addEntity(): QueryGroup {
        $data   = $this->getNonExistingEntityData();
        $entity = $this->Table->newEntity( $data );

        $saved = $this->Table->saveOrFail( $entity );

        return $this->Table->get( $saved->id, [
            'contain' => self::CONTAINS,
        ] );
    }

    private function getNonExistingEntityData(): array {
        $data = [
            'code' => 'myquery-group',
        ];

        $query = $this->getEntityQueryFromArray( $data );

        $this->Table->deleteManyOrFail( $query );

        return $data;
    }

    private function getEntityQueryFromArray( array $data ): Query {
        return $this->Table->find()
            ->contain( self::CONTAINS )
            ->where( [ self::TABLE . '.code' => $data['code'] ] );
    }

    private function assertEntityExists( array $expected ): void {
        $query = $this->getEntityQueryFromArray( $expected );

        self::assertEquals( 1, $query->count() );

        /** @var QueryGroup $dbData */
        $dbData = $query->first();
        self::assertEquals( $dbData->code, $expected['code'] );
    }
}
