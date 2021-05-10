<?php
declare( strict_types=1 );

namespace App\Test\TestCase\Controller;

use App\Model\Entity\ExperimentSite;
use App\Model\Table\ExperimentSitesTable;
use App\Test\Util\AuthenticateTrait;
use App\Test\Util\DependsOnFixtureTrait;
use App\Test\Util\ExperimentSiteTrait;
use Cake\ORM\Query;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\ExperimentSitesController Test Case
 *
 * @uses \App\Controller\ExperimentSitesController
 */
class ExperimentSitesControllerTest extends TestCase {
    use IntegrationTestTrait;
    use DependsOnFixtureTrait;
    use AuthenticateTrait;
    use ExperimentSiteTrait;

    private const ENDPOINT = '/experiment-sites';
    private const TABLE = 'ExperimentSites';
    private const CONTAINS = [
    ];

    protected array $dependsOnFixture = self::CONTAINS;
    protected ExperimentSitesTable $Table;

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
            ->orderDesc( self::TABLE . '.id' )
            ->limit( 100 )
            ->all();

        /** @var ExperimentSite $first */
        $first = $query->first();
        $last  = $query->last();

        $this->assertResponseContains( $first->name );
        $this->assertResponseContains( $last->name );
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

        $this->assertResponseContains( $entity->name );
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
            'name' => 'changed',
        ];

        $testEntity = $this->getEntityQueryFromArray( $data )
                           ->find( 'all' )
                           ->first();
        if ( $testEntity ) {
            $this->Table->delete( $testEntity );
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

    private function addEntity(): ExperimentSite {
        $data   = $this->getNonExistingEntityData();
        $entity = $this->Table->newEntity( $data );

        $saved = $this->Table->saveOrFail( $entity );

        return $this->Table->get( $saved->id, [
            'contain' => self::CONTAINS,
        ] );
    }

    private function getNonExistingEntityData(): array {
        $data = [
            'name' => 'myexperiment-site',
        ];

        $query = $this->getEntityQueryFromArray( $data );

        $this->Table->deleteManyOrFail( $query );

        return $data;
    }

    private function getEntityQueryFromArray( array $data ): Query {
        return $this->Table->find()
                           ->contain( self::CONTAINS )
                           ->where( [ self::TABLE . '.name' => $data['name'] ] );
    }

    private function assertEntityExists( array $expected ): void {
        $query = $this->getEntityQueryFromArray( $expected );

        self::assertEquals( 1, $query->count() );

        /** @var ExperimentSite $dbData */
        $dbData = $query->first();
        self::assertEquals( $dbData->name, $expected['name'] );
    }

    /**
     * Test select method
     *
     * @return void
     */
    public function testSelect_get(): void {
        $entity = $this->addEntity();

        $this->get( self::ENDPOINT . "/select" );

        $this->assertResponseContains( $entity->name );

    }

    /**
     * Test select method
     *
     * @return void
     */
    public function testSelect_post(): void {
        $entity = $this->addEntity();

        $this->session( [ 'redirect_after_action' => '/crossings' ] );

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post( self::ENDPOINT . "/select", [
            'experiment_site_id' => $entity->id,
        ] );

        $this->assertSession( $entity->id, 'experiment_site_id' );
        $this->assertSession( $entity->name, 'experiment_site_name' );

        $this->assertRedirect( [ 'controller' => 'Crossings', 'action' => 'index' ] );
    }
}
