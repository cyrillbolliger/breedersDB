<?php

namespace App\Test\TestCase\Controller\Shared;

use App\Model\Entity\Batch;
use App\Model\Entity\Variety;
use Cake\ORM\Query;

trait VarietiesControllerTestTrait
{
    private function addEntity($code = '999'): Variety
    {
        $data = $this->getNonExistingEntityData($code);
        $entity = $this->Table->newEntity($data);

        $saved = $this->Table->saveOrFail($entity);

        return $this->Table->get($saved->id, [
            'contain' => self::CONTAINS,
        ]);
    }

    private function getNonExistingEntityData($code = '999'): array
    {
        /** @var Batch $batch */
        $batch = $this->getTable('Batches')
            ->find()
            ->firstOrFail();

        $data = [
            'code' => $code,
            'official_name' => 'supervariety',
            'acronym' => 'supi',
            'plant_breeder' => 'Hugo',
            'registration' => 'worldwide',
            'description' => 'this variety is so super',
            'batch_id' => $batch->id,
        ];

        $query = $this->getEntityQueryFromArray($data);
        $this->Table->deleteManyOrFail($query);

        return $data;
    }

    private function getEntityQueryFromArray(array $data): Query
    {
        return $this->Table->find()
            ->contain(self::CONTAINS)
            ->where([
                        self::TABLE . '.code' => $data['code'],
                        self::TABLE . '.batch_id' => $data['batch_id']
                    ]);
    }

    private function assertEntityExists(array $expected): void
    {
        $query = $this->getEntityQueryFromArray($expected);

        self::assertEquals(1, $query->count());

        /** @var Variety $dbData */
        $dbData = $query->firstOrFail();
        self::assertEquals( $dbData->code, $expected['code'] );
        self::assertEquals( $dbData->description, $expected['description'] );
        self::assertEquals( $dbData->batch_id, $expected['batch_id'] );

        if ( array_key_exists( 'official_name', $expected ) ) {
            self::assertEquals( $dbData->official_name, $expected['official_name'] );
        }
        if ( array_key_exists( 'acronym', $expected ) ) {
            self::assertEquals( $dbData->acronym, $expected['acronym'] );
        }
        if ( array_key_exists( 'plant_breeder', $expected ) ) {
            self::assertEquals( $dbData->plant_breeder, $expected['plant_breeder'] );
        }
        if ( array_key_exists( 'registration', $expected ) ) {
            self::assertEquals( $dbData->registration, $expected['registration'] );
        }
    }
}
