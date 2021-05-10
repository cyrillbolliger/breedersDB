<?php
declare( strict_types=1 );

namespace App\Test\TestCase\Controller;

use App\Model\Entity\MarkFormProperty;
use App\Model\Entity\MarkScannerCode;
use App\Model\Table\MarkScannerCodesTable;
use App\Test\Util\AjaxTrait;
use App\Test\Util\AuthenticateTrait;
use App\Test\Util\DependsOnFixtureTrait;
use App\Test\Util\ExperimentSiteTrait;
use Cake\ORM\Query;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\MarkScannerCodesController Test Case
 *
 * @uses \App\Controller\MarkScannerCodesController
 */
class MarkScannerCodesControllerTest extends TestCase {
    use IntegrationTestTrait;
    use DependsOnFixtureTrait;
    use AuthenticateTrait;
    use ExperimentSiteTrait;
    use AjaxTrait;

    private const ENDPOINT = '/mark-scanner-codes';
    private const TABLE = 'MarkScannerCodes';
    private const CONTAINS = [
        'MarkFormProperties',
    ];

    protected array $dependsOnFixture = [ 'MarkScannerCodes' ] + self::CONTAINS;
    protected MarkScannerCodesTable $Table;

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

        /** @var MarkScannerCode $first */
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
        $this->assertResponseContains( $entity->mark_form_property->name );
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

        $query = $this->getEntityQueryFromArray( $data );
        $this->assertRedirect( [ 'action' => 'print', $query->first()->id, 'add' ] );

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
            'mark_value'            => '9',
            'mark_form_property_id' => $entity->mark_form_property_id,
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

    private function addEntity(): MarkScannerCode {
        $data         = $this->getNonExistingEntityData();
        $data['code'] = $this->Table->getNextFreeCode();
        $entity       = $this->Table->newEntity( $data );

        $saved = $this->Table->saveOrFail( $entity );

        return $this->Table->get( $saved->id, [
            'contain' => self::CONTAINS,
        ] );
    }

    private function getNonExistingEntityData(): array {
        /** @var MarkFormProperty $property */
        $property = $this->getTable( 'MarkFormProperties' )
                         ->find()
                         ->where( [
                             'field_type' => 'INTEGER',
                         ] )
                         ->firstOrFail();

        $data = [
            'mark_value'            => '7',
            'mark_form_property_id' => $property->id,
        ];

        $query = $this->getEntityQueryFromArray( $data );

        $this->Table->deleteManyOrFail( $query );

        return $data;
    }

    private function getEntityQueryFromArray( array $data ): Query {
        return $this->Table->find()
                           ->contain( self::CONTAINS )
                           ->where( [ self::TABLE . '.mark_value' => $data['mark_value'] ] )
                           ->andWhere( [ self::TABLE . '.mark_form_property_id' => $data['mark_form_property_id'] ] );
    }

    private function assertEntityExists( array $expected ): void {
        $query = $this->getEntityQueryFromArray( $expected );

        self::assertEquals( 1, $query->count() );

        /** @var MarkScannerCode $dbData */
        $dbData = $query->first();
        self::assertEquals( $dbData->mark_value, $expected['mark_value'] );
        self::assertEquals( $dbData->mark_form_property_id, $expected['mark_form_property_id'] );
    }

    /**
     * Test filter method
     *
     * @return void
     */
    public function testFilter(): void {
        $entity = $this->addEntity();

        $this->setAjaxHeader();
        $this->get( self::ENDPOINT . '/filter?fields%5B%5D=mark_form_property_id&term=' . $entity->mark_form_property_id );
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
        $this->get( self::ENDPOINT . '/filter?fields%5B%5D=mark_form_property_id&term=123456789' );
        $this->assertResponseSuccess();
        $this->assertResponseContains( 'nothing_found' );
    }

    /**
     * Test getMark method
     */
    public function testGetMark(): void {
        $entity = $this->addEntity();

        $this->setAjaxHeader();
        $this->get( self::ENDPOINT . '/get-mark?term=' . $entity->code );

        $this->assertResponseSuccess();

        $expected = [
            'data' => [
                'mark_value'            => $entity->mark_value,
                'mark_form_property_id' => $entity->mark_form_property_id,
            ],
        ];
        $expected = json_encode( $expected );
        self::assertEquals( $expected, (string) $this->_response->getBody() );
    }

    public function testPrint(): void {
        $entity = $this->addEntity();
        $query  = $this->getEntityQueryFromArray( $entity->toArray() );

        $this->get( self::ENDPOINT . '/print/' . $query->first()->id . '/mark-scanner-codes' );

        $this->assertResponseSuccess();
        $this->assertResponseRegExp(
            '/\${\^XA\^BY.*\^BC.*\^FD' . $entity->code . '\^FS.*\^FD' . $entity->mark_form_property->name . ': ' . $entity->mark_value . '\^FS\^XZ}\$/'
        );
    }

    public function testPrintSubmit(): void {
        $this->get( self::ENDPOINT . '/print-submit/' );

        $this->assertResponseSuccess();
        $this->assertResponseRegExp(
            '/\${\^XA\^BY.*\^BC.*\^FDSUBMIT\^FS.*\^FDSUBMIT\^FS\^XZ}\$/'
        );
    }
}
