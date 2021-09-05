<?php
declare( strict_types=1 );

namespace App\Test\TestCase\Controller;

use App\Controller\MarkFormsController;
use App\Model\Entity\MarkForm;
use App\Model\Table\MarkFormsTable;
use App\Test\TestCase\Controller\Shared\MarkFormsControllerTestTrait;
use App\Test\Util\AjaxTrait;
use App\Test\Util\AuthenticateTrait;
use App\Test\Util\DependsOnFixtureTrait;
use App\Test\Util\ExperimentSiteTrait;
use Cake\Collection\Collection;
use Cake\ORM\Query;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\MarkFormsController Test Case
 *
 * @uses \App\Controller\MarkFormsController
 */
class MarkFormsControllerTest extends TestCase {
    use IntegrationTestTrait;
    use DependsOnFixtureTrait;
    use AuthenticateTrait;
    use ExperimentSiteTrait;
    use AjaxTrait;
    use MarkFormsControllerTestTrait;

    private const ENDPOINT = '/mark-forms';
    private const TABLE = 'MarkForms';
    private const CONTAINS = [
        'MarkFormFields',
    ];

    protected array $dependsOnFixture = self::CONTAINS;
    protected MarkFormsTable $Table;

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

        /** @var MarkForm $first */
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
    public function testAdd(): void {
        $data = $this->getNonExistingEntityData();

        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->setUnlockedFields( [ 'mark_form_fields' ] );

        $this->post( self::ENDPOINT . '/add', $data );

        $this->assertResponseSuccess();
        $this->assertEntityExists( $data );

        $this->deleteWithAssociatedData( $this->getEntityQueryFromArray( $data ) );
    }

    /**
     * Test edit method
     *
     * @return void
     */
    public function testEdit(): void {
        $entity = $this->addEntity();

        $properties = $this->getTable( 'MarkFormProperties' )->find()->all();
        $data       = [
            'name'             => 'changed',
            'description'      => 'another random string',
            'mark_form_fields' => [
                'mark_form_properties' => [
                    $properties->last()->id => [
                        'mark_values' => [
                            'value' => ''
                        ]
                    ]
                ]
            ]
        ];

        $testEntity = $this->getEntityQueryFromArray( $data )
                           ->find( 'all', [ 'withDeleted' ] )
                           ->first();
        if ( $testEntity ) {
            $this->Table->hardDelete( $testEntity );
        }

        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->setUnlockedFields( [ 'mark_form_fields' ] );

        $this->post( self::ENDPOINT . '/edit/' . $entity->id, $data );

        $this->assertResponseSuccess();
        $this->assertEntityExists( $data );

        $this->deleteWithAssociatedData( $this->getEntityQueryFromArray( $data ) );
    }

    /**
     * Test delete method
     *
     * @return void
     */
    public function testDelete_failed(): void {
        $entity = $this->addEntity();

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->delete( self::ENDPOINT . "/delete/{$entity->id}" );
        $this->assertResponseSuccess();

        $query = $this->getEntityQueryFromArray( $entity->toArray() );
        self::assertEquals( 1, $query->count() );
    }

    /**
     * Test delete method
     *
     * @return void
     */
    public function testDelete_empty_success(): void {
        $data = $this->getNonExistingEntityData();
        unset($data['mark_form_fields']);
        $entity = $this->Table->newEntity( $data );
        $saved = $this->Table->saveOrFail( $entity );

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->delete( self::ENDPOINT . "/delete/{$saved->id}" );
        $this->assertResponseSuccess();

        $query = $this->getEntityQueryFromArray( $saved->toArray() );
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
        $this->get( self::ENDPOINT . '/filter?fields%5B%5D=name&term=thisformdoesnotexist' );
        $this->assertResponseSuccess();
        $this->assertResponseContains( 'nothing_found' );
    }
}
