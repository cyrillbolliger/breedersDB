<?php
declare( strict_types=1 );

namespace App\Test\TestCase\Controller;

use App\Model\Entity\Row;
use App\Model\Table\RowsTable;
use App\Test\Util\AjaxTrait;
use App\Test\Util\AuthenticateTrait;
use App\Test\Util\DependsOnFixtureTrait;
use App\Test\Util\ExperimentSiteTrait;
use Cake\ORM\Query;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\RowsController Test Case
 *
 * @uses \App\Controller\RowsController
 */
class RowsControllerTest extends TestCase {
    use IntegrationTestTrait;
    use DependsOnFixtureTrait;
    use AuthenticateTrait;
    use ExperimentSiteTrait;
    use AjaxTrait;

    private const ENDPOINT = '/rows';
    private const TABLE = 'Rows';
    private const CONTAINS = [
        'Trees',
    ];

    protected array $dependsOnFixture = self::CONTAINS;
    protected RowsTable $Table;

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

        /** @var Row $first */
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
            'code'            => 'changed',
            'note'            => 'another random string',
            'date_created'    => '01.01.2020',
            'date_eliminated' => '11.11.2020',
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
    public function testFilter(): void {
        $entity = $this->addEntity();

        $this->setAjaxHeader();
        $this->get( self::ENDPOINT . '/filter?fields%5B%5D=code&term=' . $entity->code );
        $this->assertResponseSuccess();
        $this->assertResponseContains( $entity->code );
    }

    /**
     * Test filter method
     *
     * @return void
     */
    public function testFilter_nothing(): void {
        $this->setAjaxHeader();
        $this->get( self::ENDPOINT . '/filter?fields%5B%5D=code&term=thisrowdoesnotexist' );
        $this->assertResponseSuccess();
        $this->assertResponseContains( 'nothing_found' );
    }

    private function addEntity(): Row {
        $data   = $this->getNonExistingEntityData();
        $entity = $this->Table->newEntity( $data );

        $saved = $this->Table->saveOrFail( $entity );

        return $this->Table->get( $saved->id, [
            'contain' => self::CONTAINS,
        ] );
    }

    private function getNonExistingEntityData(): array {
        $data = [
            'code'            => 'myrow',
            'note'            => 'what a wonderfull row',
            'date_created'    => '01.01.1970',
            'date_eliminated' => '01.01.2020',
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

        /** @var Row $dbData */
        $dbData = $query->first();
        self::assertEquals( $dbData->code, $expected['code'] );
        self::assertEquals( $dbData->note, $expected['note'] );
        self::assertEquals( $dbData->date_created->format( 'd.m.Y' ), $expected['date_created'] );
        self::assertEquals( $dbData->date_eliminated->format( 'd.m.Y' ), $expected['date_eliminated'] );
    }
}
