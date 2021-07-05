<?php
declare( strict_types=1 );

namespace App\Test\TestCase\Controller;

use App\Model\Entity\Grafting;
use App\Model\Table\GraftingsTable;
use App\Test\Util\AuthenticateTrait;
use App\Test\Util\DependsOnFixtureTrait;
use App\Test\Util\ExperimentSiteTrait;
use Cake\ORM\Query;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\GraftingsController Test Case
 *
 * @uses \App\Controller\GraftingsController
 */
class GraftingsControllerTest extends TestCase {
    use IntegrationTestTrait;
    use DependsOnFixtureTrait;
    use AuthenticateTrait;
    use ExperimentSiteTrait;

    private const ENDPOINT = '/graftings';
    private const TABLE = 'Graftings';
    private const CONTAINS = [
    ];

    protected array $dependsOnFixture = self::CONTAINS;
    protected GraftingsTable $Table;

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

        /** @var Grafting $first */
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

    private function addEntity(): Grafting {
        $data   = $this->getNonExistingEntityData();
        $entity = $this->Table->newEntity( $data );

        $saved = $this->Table->saveOrFail( $entity );

        return $this->Table->get( $saved->id, [
            'contain' => self::CONTAINS,
        ] );
    }

    private function getNonExistingEntityData(): array {
        $data = [
            'name' => 'mygrafting',
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

        /** @var Grafting $dbData */
        $dbData = $query->first();
        self::assertEquals( $dbData->name, $expected['name'] );
    }
}
