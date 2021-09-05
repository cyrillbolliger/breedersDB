<?php

namespace App\Test\TestCase\Controller\Shared;

use App\Model\Entity\ExperimentSite;
use App\Model\Entity\Grafting;
use App\Model\Entity\Rootstock;
use App\Model\Entity\Row;
use App\Model\Entity\Tree;
use App\Model\Entity\Variety;
use Cake\ORM\Query;

trait TreesControllerTestTrait
{
    private function addEntity(): Tree {
        $data   = $this->getNonExistingEntityData();
        $entity = $this->Table->newEntity( $data );

        $saved = $this->Table->saveOrFail( $entity );

        return $this->Table->get( $saved->id, [
            'contain' => self::CONTAINS,
        ] );
    }

    private function getNonExistingEntityData(): array {
        /** @var Variety $variety */
        $variety = $this->getTable( 'Varieties' )
                        ->find()
                        ->firstOrFail();

        /** @var Rootstock $rootstock */
        $rootstock = $this->getTable( 'Rootstocks' )
                          ->find()
                          ->firstOrFail();

        /** @var Grafting $grafting */
        $grafting = $this->getTable( 'Graftings' )
                         ->find()
                         ->firstOrFail();

        /** @var Row $row */
        $row = $this->getTable( 'Rows' )
                    ->find()
                    ->firstOrFail();

        /** @var ExperimentSite $site */
        $site = $this->getTable( 'ExperimentSites' )
                     ->find()
                     ->firstOrFail();

        $data = [
            'publicid'           => '12345678',
            'date_grafted'       => '01.01.2021',
            'date_planted'       => '01.01.2021',
            'date_eliminated'    => null,
            'date_labeled'       => '01.01.2021',
            'genuine_seedling'   => true,
            'migrated_tree'      => false,
            'offset'             => 12.3,
            'dont_eliminate'     => false,
            'note'               => 'this is an important note',
            'variety_id'         => $variety->id,
            'rootstock_id'       => $rootstock->id,
            'grafting_id'        => $grafting->id,
            'row_id'             => $row->id,
            'experiment_site_id' => $site->id,
        ];

        $query = $this->getEntityQueryFromArray( $data );

        $this->Table->deleteManyOrFail( $query );

        return $data;
    }

    private function getEntityQueryFromArray( array $data ): Query {
        return $this->Table->find()
                           ->contain( self::CONTAINS )
                           ->where( [ self::TABLE . '.publicid' => $data['publicid'] ] );
    }

    private function assertEntityExists( array $expected ): void {
        $query = $this->getEntityQueryFromArray( $expected );

        self::assertEquals( 1, $query->count() );

        /** @var Tree $dbData */
        $dbData = $query->first();
        self::assertEquals( $dbData->publicid, $expected['publicid'] );
        self::assertEquals( $dbData->date_grafted->format( 'd.m.Y' ), $expected['date_grafted'] );
        self::assertEquals( $dbData->date_planted->format( 'd.m.Y' ), $expected['date_planted'] );
        self::assertEquals( $dbData->date_eliminated, $expected['date_eliminated'] );
        self::assertEquals( $dbData->date_labeled->format( 'd.m.Y' ), $expected['date_labeled'] );
        self::assertEquals( $dbData->genuine_seedling, $expected['genuine_seedling'] );
        self::assertEquals( $dbData->migrated_tree, $expected['migrated_tree'] );
        self::assertEquals( $dbData->offset, $expected['offset'] );
        self::assertEquals( $dbData->dont_eliminate, $expected['dont_eliminate'] );
        self::assertEquals( $dbData->note, $expected['note'] );
        self::assertEquals( $dbData->variety_id, $expected['variety_id'] );
        self::assertEquals( $dbData->rootstock_id, $expected['rootstock_id'] );
        self::assertEquals( $dbData->grafting_id, $expected['grafting_id'] );
        self::assertEquals( $dbData->row_id, $expected['row_id'] );
        self::assertEquals( $dbData->experiment_site_id, $expected['experiment_site_id'] );
    }

    private function deleteWithAssociated( Query $query ): void {
        $marksTable      = $this->getTable( 'Marks' );
        $markValuesTable = $this->getTable( 'MarkValues' );
        foreach ( $query->all() as $tree ) {
            foreach ( $tree->marks as $mark ) {
                $mark = $marksTable->get( $mark->id, [ 'contain' => [ 'MarkValues' ] ] );
                $markValuesTable->deleteManyOrFail( $mark->mark_values );
                $marksTable->deleteOrFail( $mark );
            }
        }
        $this->Table->deleteManyOrFail( $query );
    }
}
