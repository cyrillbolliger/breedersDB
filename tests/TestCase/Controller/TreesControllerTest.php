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
use App\Test\TestCase\Controller\Shared\TreesControllerTestTrait;
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
    use TreesControllerTestTrait;

    private const ENDPOINT = '/trees';
    private const TABLE = 'Trees';
    private const CONTAINS = [
        'Varieties',
        'Rows',
        'ExperimentSites',
        'Rootstocks',
        'Graftings',
        'Marks',
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
            ->limit( 100 )
            ->all();

        /** @var Tree $first */
        $first = $query->first();
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
            'date_grafted'       => '01.02.2020',
            'date_planted'       => '01.03.2020',
            'date_eliminated'    => null,
            'date_labeled'       => '01.04.2020',
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

        $testEntities = $this->getEntityQueryFromArray( $data )
                           ->find( 'all', [ 'withDeleted' ] );
        foreach($testEntities as $testEntity) {
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
            'date_planted' => '11.12.2020',
            'offset'       => 123.4,
            'note'         => 'we love planting trees',
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
            'date_eliminated' => '11.10.2020',
            'note'            => 'dead',
        ];

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $query = $this->getEntityQueryFromArray( [ 'publicid' => '#' . $entity->publicid ] );
        $this->deleteWithAssociated( $query );

        $this->post( self::ENDPOINT . '/update/' . $entity->id, $data );

        $this->assertResponseSuccess();

        self::assertEquals( 1, $query->count() );

        /** @var Tree $dbData */
        $dbData = $query->first();
        self::assertEquals( $dbData->publicid, '#' . $entity->publicid );
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

        $this->get( self::ENDPOINT . "/print/{$entity->id}/view/{$entity->id}" );
        $this->assertResponseSuccess();
        $this->assertResponseContains( 'data-zpl' );
        $this->assertResponseContains( $entity->publicid );
    }
}
