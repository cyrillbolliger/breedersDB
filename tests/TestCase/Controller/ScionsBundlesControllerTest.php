<?php
declare( strict_types=1 );

namespace App\Test\TestCase\Controller;

use App\Model\Entity\ScionsBundle;
use App\Model\Entity\Variety;
use App\Model\Table\ScionsBundlesTable;
use App\Test\Util\AjaxTrait;
use App\Test\Util\AuthenticateTrait;
use App\Test\Util\DependsOnFixtureTrait;
use App\Test\Util\ExperimentSiteTrait;
use Cake\ORM\Query;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\ScionsBundlesController Test Case
 *
 * @uses \App\Controller\ScionsBundlesController
 */
class ScionsBundlesControllerTest extends TestCase {
    use IntegrationTestTrait;
    use DependsOnFixtureTrait;
    use AuthenticateTrait;
    use ExperimentSiteTrait;
    use AjaxTrait;

    private const ENDPOINT = '/scions-bundles';
    private const TABLE = 'ScionsBundles';
    private const CONTAINS = [
        'Varieties',
    ];

    protected array $dependsOnFixture = self::CONTAINS;
    protected ScionsBundlesTable $Table;

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

        /** @var ScionsBundle $first */
        $first = $query->first();
        $last  = $query->last();

        $this->assertResponseContains( $first->code );
        $this->assertResponseContains( $last->code );
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

        $this->assertResponseContains( $entity->code );
        $this->assertResponseContains( $entity->variety->convar );
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
     * Test edit method
     *
     * @return void
     */
    public function testEdit(): void {
        $entity = $this->addEntity();

        $data = [
            'code'                   => 'changed',
            'numb_scions'            => '999',
            'date_scions_harvest'    => '01.01.2020',
            'descents_publicid_list' => 'just a random string',
            'note'                   => 'another random string',
            'external_use'           => false,
            'variety_id'             => $entity->variety_id,
        ];

        $testEntity = $this->getEntityQueryFromArray( $data )
                           ->find( 'all', [ 'withDeleted' ] )
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
    public function testFilter_code(): void {
        $entity = $this->addEntity();

        $this->setAjaxHeader();
        $this->get( self::ENDPOINT . '/filter?fields%5B%5D=code&fields%5B%5D=convar&term=' . $entity->code );
        $this->assertResponseSuccess();
        $this->assertResponseContains( $entity->code );
    }

    /**
     * Test filter method
     *
     * @return void
     */
    public function testFilter_convar(): void {
        $entity = $this->addEntity();

        $this->setAjaxHeader();
        $this->get( self::ENDPOINT . '/filter?fields%5B%5D=code&fields%5B%5D=convar&term=' . $entity->variety->convar );
        $this->assertResponseSuccess();
        $this->assertResponseContains( $entity->variety->convar );
    }

    /**
     * Test filter method
     *
     * @return void
     */
    public function testFilter_nothing(): void {
        $this->setAjaxHeader();
        $this->get( self::ENDPOINT . '/filter?fields%5B%5D=code&fields%5B%5D=convar&term=thisdoesnotexist' );
        $this->assertResponseSuccess();
        $this->assertResponseContains( 'nothing_found' );
    }

    private function addEntity(): ScionsBundle {
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

        $data = [
            'code'                   => 'SB_123',
            'numb_scions'            => '11',
            'date_scions_harvest'    => '11.11.2011',
            'descents_publicid_list' => '00000152, 00000231, 00000015',
            'note'                   => 'Gerhard Kerluke',
            'external_use'           => true,
            'variety_id'             => $variety->id,
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

        /** @var ScionsBundle $dbData */
        $dbData = $query->first();
        self::assertEquals( $dbData->code, $expected['code'] );
        self::assertEquals( $dbData->numb_scions, $expected['numb_scions'] );
        self::assertEquals( $dbData->date_scions_harvest->format( 'd.m.Y' ), $expected['date_scions_harvest'] );
        self::assertEquals( $dbData->descents_publicid_list, $expected['descents_publicid_list'] );
        self::assertEquals( $dbData->note, $expected['note'] );
        self::assertEquals( $dbData->external_use, $expected['external_use'] );
        self::assertEquals( $dbData->variety_id, $expected['variety_id'] );
    }
}
