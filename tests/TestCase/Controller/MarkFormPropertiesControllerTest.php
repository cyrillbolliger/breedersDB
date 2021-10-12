<?php
declare( strict_types=1 );

namespace App\Test\TestCase\Controller;

use App\Model\Entity\MarkFormProperty;
use App\Model\Entity\MarkFormPropertyType;
use App\Model\Table\MarkFormPropertiesTable;
use App\Test\TestCase\Controller\Shared\MarkFormPropertiesControllerTestTrait;
use App\Test\Util\AjaxTrait;
use App\Test\Util\AuthenticateTrait;
use App\Test\Util\DependsOnFixtureTrait;
use App\Test\Util\ExperimentSiteTrait;
use Cake\ORM\Query;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\MarkFormPropertiesController Test Case
 *
 * @uses \App\Controller\MarkFormPropertiesController
 */
class MarkFormPropertiesControllerTest extends TestCase {
    use IntegrationTestTrait;
    use DependsOnFixtureTrait;
    use AuthenticateTrait;
    use ExperimentSiteTrait;
    use AjaxTrait;
    use MarkFormPropertiesControllerTestTrait;

    private const ENDPOINT = '/mark-form-properties';
    private const TABLE = 'MarkFormProperties';
    private const CONTAINS = [
        'MarkFormPropertyTypes',
        'MarkFormFields',
        'MarkValues',
    ];

    protected array $dependsOnFixture = [ 'MarkFormProperties' ] + self::CONTAINS;
    protected MarkFormPropertiesTable $Table;

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

        /** @var MarkFormProperty $first */
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

        // todo: add mark form and verify relation
        $this->markTestIncomplete();
    }

    /**
     * Test add method
     *
     * @return void
     */
    public function testAdd_integer(): void {
        $data               = $this->getNonExistingEntityData();
        $data['field_type'] = 'INTEGER';
        $data['min']        = '1';
        $data['max']        = '9';
        $data['step']       = '1';

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post( self::ENDPOINT . '/add', $data );

        unset( $data['min'], $data['max'], $data['step'] );
        $data['validation_rule'] = [
            'min'  => '1',
            'max'  => '9',
            'step' => '1'
        ];

        $this->assertResponseSuccess();
        $this->assertEntityExists( $data );

        $this->Table->deleteManyOrFail( $this->getEntityQueryFromArray( $data ) );
    }

    /**
     * Test add method
     *
     * @return void
     */
    public function testAdd_float(): void {
        $data               = $this->getNonExistingEntityData();
        $data['field_type'] = 'FLOAT';
        $data['min']        = '1';
        $data['max']        = '9';
        $data['step']       = '0.5';

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post( self::ENDPOINT . '/add', $data );

        unset( $data['min'], $data['max'], $data['step'] );
        $data['validation_rule'] = [
            'min'  => '1',
            'max'  => '9',
            'step' => '0.5'
        ];

        $this->assertResponseSuccess();
        $this->assertEntityExists( $data );

        $this->Table->deleteManyOrFail( $this->getEntityQueryFromArray( $data ) );
    }

    /**
     * Test add method
     *
     * @return void
     */
    public function testAdd_other(): void {
        $data               = $this->getNonExistingEntityData();
        $data['field_type'] = 'VARCHAR';

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post( self::ENDPOINT . '/add', $data );

        $data['validation_rule'] = [];

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
            'name'                       => 'changed',
            'min'                        => '0',
            'max'                        => '10',
            'step'                       => '2',
            'field_type'                 => 'INTEGER',
            'note'                       => 'this was changed',
            'mark_form_property_type_id' => $entity->mark_form_property_type_id,
            'tree_property'              => false,
            'variety_property'           => false,
            'batch_property'             => true,
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

        unset( $data['min'], $data['max'], $data['step'] );
        $data['validation_rule'] = [
            'min'  => '0',
            'max'  => '10',
            'step' => '2'
        ];

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
     * Test get method
     *
     * @return void
     */
    public function testGet_editForm(): void {
        $entity = $this->addEntity();

        $this->get( self::ENDPOINT . "/get/{$entity->id}/field_edit_form_mode" );

        $this->assertResponseSuccess();
        $this->assertResponseCode( 200 );

        $this->assertResponseContains( $entity->name );
        $this->assertResponseContains( 'name="mark_form_fields[mark_form_properties][' . $entity->id . '][mark_values][value]"' );
        $this->assertResponseContains( 'sortable_element deletable_element' );
    }

    /**
     * Test get method
     *
     * @return void
     */
    public function testGet_default(): void {
        $entity = $this->addEntity();

        $this->get( self::ENDPOINT . "/get/{$entity->id}/default" );

        $this->assertResponseSuccess();
        $this->assertResponseCode( 200 );

        $this->assertResponseContains( $entity->name );
        $this->assertResponseContains( 'name="mark_form_fields[mark_form_properties][' . $entity->id . '][mark_values][value]"' );
        $this->assertResponseNotContains( 'sortable_element deletable_element' );
    }

    /**
     * Test filter method
     *
     * @return void
     */
    public function testFilter(): void {
        $entity = $this->addEntity();

        $this->setAjaxHeader();
        $this->get( self::ENDPOINT . '/filter?fields%5B%5D=name&term=' . $entity->name );
        $this->assertResponseSuccess();
        $this->assertResponseContains( $entity->name );
    }

    /**
     * Test filter method
     *
     * @return void
     */
    public function testFilter_nothing(): void {
        $this->setAjaxHeader();
        $this->get( self::ENDPOINT . '/filter?fields%5B%5D=name&term=thisdoesnotexist' );
        $this->assertResponseSuccess();
        $this->assertResponseContains( 'nothing_found' );
    }
}
