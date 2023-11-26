<?php

declare(strict_types=1);

namespace App\Test\TestCase\Controller\Shared;

use App\Model\Entity\Batch;
use App\Model\Entity\Crossing;
use Cake\ORM\Query;

trait BatchesControllerTestTrait
{
    private function addEntity(string $code = '99A'): Batch
    {
        $data = $this->getNonExistingEntityData($code);

        $data['date_sowed'] = date_create_from_format('d.m.Y', $data['date_sowed'])->format('Y-m-d');
        $data['date_planted'] = date_create_from_format('d.m.Y', $data['date_planted'])->format('Y-m-d');

        $entity = $this->Table->newEntity($data);

        $saved = $this->Table->saveOrFail($entity);

        return $this->Table->get($saved->id, [
            'contain' => self::CONTAINS,
        ]);
    }

    private function getNonExistingEntityData(string $code = '99A'): array
    {
        /** @var Crossing $crossing */
        $crossing = $this->getTable('Crossings')
            ->find()
            ->firstOrFail();

        $data = [
            'crossing_id' => $crossing->id,
            'code' => $code,
            'date_sowed' => '01.03.2021',
            'numb_seeds_sowed' => 123,
            'numb_sprouts_grown' => 5,
            'seed_tray' => '1',
            'date_planted' => '02.03.2021',
            'numb_sprouts_planted' => 4,
            'patch' => 'The new patch',
            'note' => 'This is very important',
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
                        self::TABLE . '.crossing_id' => $data['crossing_id'],
                        self::TABLE . '.code' => $data['code']
                    ]);
    }

    private function assertEntityExists(array $expected): void
    {
        $query = $this->getEntityQueryFromArray($expected);

        self::assertEquals(1, $query->count());

        /** @var Batch $dbData */
        $dbData = $query->first();
        self::assertEquals( $dbData->date_sowed, $expected['date_sowed'] );
        self::assertEquals( $dbData->numb_seeds_sowed, $expected['numb_seeds_sowed'] );
        self::assertEquals( $dbData->numb_sprouts_grown, $expected['numb_sprouts_grown'] );
        self::assertEquals( $dbData->seed_tray, $expected['seed_tray'] );
        self::assertEquals( $dbData->date_planted, $expected['date_planted'] );
        self::assertEquals( $dbData->numb_sprouts_planted, $expected['numb_sprouts_planted'] );
        self::assertEquals( $dbData->patch, $expected['patch'] );
        self::assertEquals( $dbData->note, $expected['note'] );
    }
}
