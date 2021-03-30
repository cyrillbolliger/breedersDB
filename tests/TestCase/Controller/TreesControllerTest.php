<?php
declare( strict_types=1 );

namespace App\Test\TestCase\Controller;

use App\Model\Entity\ExperimentSite;
use App\Model\Entity\Grafting;
use App\Model\Entity\Rootstock;
use App\Model\Entity\Row;
use App\Model\Entity\Tree;
use App\Model\Entity\Variety;
use App\Model\Table\TreesTable;
use App\Test\Util\AjaxTrait;
use App\Test\Util\AuthenticateTrait;
use App\Test\Util\DependsOnFixtureTrait;
use App\Test\Util\ExperimentSiteTrait;
use Cake\ORM\Query;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\TreesController Test Case
 *
 * @uses \App\Controller\TreesController
 */
class TreesControllerTest extends TestCase {
    use IntegrationTestTrait;
    use DependsOnFixtureTrait;
    use AuthenticateTrait;
    use ExperimentSiteTrait;
    use AjaxTrait;

    private const ENDPOINT = '/trees';
    private const TABLE = 'Trees';
    private const CONTAINS = [
        'Varieties',
        'Rows',
        'ExperimentSites',
        'Rootstocks',
        'Graftings',
    ];

    protected array $dependsOnFixture = self::CONTAINS;
    protected TreesTable $Table;

    protected function setUp(): void {
        $this->authenticate();
        $this->setSite();
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->Table = $this->getTable( self::TABLE );
        parent::setUp();
    }

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndex(): void {
        $this->addEntity();

        $this->get( self::ENDPOINT );

        $this->assertResponseSuccess();
        $this->assertResponseCode( 200 );

        $query = $this->Table
            ->find()
            ->orderDesc( self::TABLE . '.modified' )
            ->limit( 100 );

        /** @var Tree $first */
        $first = $query->firstOrFail();
        $last  = $query->last();

        $this->assertResponseContains( $first->publicid );
        $this->assertResponseContains( $last->publicid );
    }

    /**
     * Test view method
     *
     * @return void
     */
    public function testView(): void {
        $entity = $this->addEntity();

        $this->get( self::ENDPOINT . "/view/{$entity->id}" );

        $this->assertResponseSuccess();
        $this->assertResponseCode( 200 );

        $this->assertResponseContains( $entity->publicid );
        $this->assertResponseContains( $entity->convar );

        // todo: test marks
        self::markTestIncomplete( 'Not implemented yet.' );
    }

    /**
     * Test add method
     *
     * @return void
     */
    public function testAdd(): void {
        $data = $this->getNonExistingEntityData();

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post( self::ENDPOINT . '/add', $data );

        $this->assertResponseSuccess();
        $this->assertEntityExists( $data );

        $this->Table->deleteManyOrFail( $this->getEntityQueryFromArray( $data ) );
    }

    /**
     * Test addGenuineSeedling method
     *
     * @return void
     */
    public function testAddGenuineSeedling(): void {
        $data                     = $this->getNonExistingEntityData();
        $data['genuine_seedling'] = true;

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post( self::ENDPOINT . '/add-genuine-seedling', $data );

        $this->assertResponseSuccess();
        $this->assertEntityExists( $data );

        $this->Table->deleteManyOrFail( $this->getEntityQueryFromArray( $data ) );
    }

    /**
     * Test addGraftTree method
     *
     * @return void
     */
    public function testAddGraftTree(): void {
        $data = $this->getNonExistingEntityData();

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post( self::ENDPOINT . '/add-graft-tree', $data );

        $this->assertResponseSuccess();
        $this->assertEntityExists( $data );

        $this->Table->deleteManyOrFail( $this->getEntityQueryFromArray( $data ) );
    }

    /**
     * Test edit method
     *
     * @return void
     */
    public function testEdit(): void {
        $entity = $this->addEntity();

        $data = [
            'publicid'           => '09999999',
            'date_grafted'       => '01.01.2020',
            'date_planted'       => '01.01.2020',
            'date_eliminated'    => null,
            'date_labeled'       => '01.01.2020',
            'genuine_seedling'   => false,
            'migrated_tree'      => true,
            'offset'             => 11.1,
            'dont_eliminate'     => true,
            'note'               => 'this is not an important note',
            'variety_id'         => $entity->variety_id,
            'rootstock_id'       => $entity->rootstock_id,
            'grafting_id'        => $entity->grafting_id,
            'row_id'             => $entity->row_id,
            'experiment_site_id' => $entity->experiment_site_id,
        ];

        $testEntity = $this->getEntityQueryFromArray( $data )
                           ->find('all', ['withDeleted'])
                           ->first();
        if ( $testEntity ) {
            $this->Table->hardDelete( $testEntity );
        }

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post( self::ENDPOINT . '/edit/' . $entity->id, $data );

        $this->assertResponseSuccess();
        $this->assertEntityExists( $data );

        $this->Table->deleteManyOrFail( $this->getEntityQueryFromArray( $data ) );
    }

    /**
     * Test plant method
     *
     * @return void
     */
    public function testPlant(): void {
        $entity = $this->addEntity();

        $data = [
            'date_planted'       => '11.11.2020',
            'offset'             => 123.4,
            'note'               => 'we love planting trees',
        ];

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post( self::ENDPOINT . '/update/' . $entity->id, $data );

        $this->assertResponseSuccess();

        $data['publicid'] = $entity->publicid;

        $query = $this->getEntityQueryFromArray( $data );

        self::assertEquals( 1, $query->count() );

        /** @var Tree $dbData */
        $dbData = $query->first();
        self::assertEquals( $dbData->publicid, $data['publicid'] );
        self::assertEquals( $dbData->date_planted->format( 'd.m.Y' ), $data['date_planted'] );
        self::assertEquals( $dbData->offset, $data['offset'] );
        self::assertEquals( $dbData->note, $data['note'] );
    }

    /**
     * Test eliminate method
     *
     * @return void
     */
    public function testEliminate(): void {
        $entity = $this->addEntity();

        $data = [
            'date_eliminated'    => '11.11.2020',
            'note'               => 'dead',
        ];

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $data['publicid'] = '#'.$entity->publicid;
        $query = $this->getEntityQueryFromArray( $data );
        $this->Table->deleteManyOrFail($query);

        $this->post( self::ENDPOINT . '/update/' . $entity->id, $data );

        $this->assertResponseSuccess();

        self::assertEquals( 1, $query->count() );

        /** @var Tree $dbData */
        $dbData = $query->first();
        self::assertEquals( $dbData->publicid, $data['publicid'] );
        self::assertEquals( $dbData->date_eliminated->format( 'd.m.Y' ), $data['date_eliminated'] );
        self::assertEquals( $dbData->note, $data['note'] );
    }

    /**
     * Test eliminateByScanner method
     *
     * @return void
     */
    public function testEliminateByScanner(): void {
        $this->testEliminate();
    }

    /**
     * Test getTree method
     *
     * @return void
     */
    public function testGetTree(): void {
        $entity = $this->addEntity();

        $this->setAjaxHeader();
        $this->get( self::ENDPOINT . "/getTree?fields%5B%5D=publicid&term={$entity->publicid}&element=plant_form" );

        $this->assertResponseSuccess();
        $this->assertResponseCode( 200 );

        $this->assertResponseContains( $entity->convar );
        $this->assertResponseContains( $entity->publicid );
    }

    /**
     * Test delete method
     *
     * @return void
     */
    public function testDelete(): void {
        $entity = $this->addEntity();

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->delete( self::ENDPOINT . "/delete/{$entity->id}" );
        $this->assertResponseSuccess();

        $query = $this->getEntityQueryFromArray( $entity->toArray() );
        self::assertEquals( 0, $query->count() );
    }

    /**
     * Test filter method
     *
     * @return void
     */
    public function testFilter_publicid(): void {
        $entity = $this->addEntity();

        $this->setAjaxHeader();
        $this->get( self::ENDPOINT . '/filter?fields%5B%5D=publicid&fields%5B%5D=convar&options%5Bshow_eliminated%5D=false&term=' . $entity->publicid );
        $this->assertResponseSuccess();
        $this->assertResponseContains( $entity->publicid );
        $this->assertResponseContains( $entity->convar );
    }

    /**
     * Test filter method
     *
     * @return void
     */
    public function testFilter_convar(): void {
        $entity = $this->addEntity();

        $this->setAjaxHeader();
        $this->get( self::ENDPOINT . '/filter?fields%5B%5D=publicid&fields%5B%5D=convar&options%5Bshow_eliminated%5D=false&term=' . $entity->convar );
        $this->assertResponseSuccess();
        $this->assertResponseContains( $entity->publicid );
        $this->assertResponseContains( $entity->convar );
    }

    /**
     * Test filter method
     *
     * @return void
     */
    public function testFilter_nothing(): void {
        $this->setAjaxHeader();
        $this->get( self::ENDPOINT . '/filter?fields%5B%5D=publicid&fields%5B%5D=convar&options%5Bshow_eliminated%5D=false&term=thisdoesnotexist' );
        $this->assertResponseSuccess();
        $this->assertResponseContains( 'nothing_found' );
    }

    /**
     * Test print method
     *
     * @return void
     */
    public function testPrint(): void {
        $entity = $this->addEntity();

        $this->get(self::ENDPOINT. "/print/{$entity->id}/view/{$entity->id}");
        $this->assertResponseSuccess();
        $this->assertResponseContains( 'data-zpl' );
        $this->assertResponseContains( $entity->publicid );
    }

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
}
