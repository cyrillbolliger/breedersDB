<?php
declare( strict_types=1 );

namespace App\Test\TestCase\Controller;

use App\Model\Entity\Mark;
use App\Model\Entity\MarkForm;
use App\Model\Entity\Tree;
use App\Model\Table\MarksTable;
use App\Test\TestCase\Controller\Shared\MarksControllerTestTrait;
use App\Test\Util\AjaxTrait;
use App\Test\Util\AuthenticateTrait;
use App\Test\Util\DependsOnFixtureTrait;
use App\Test\Util\ExperimentSiteTrait;
use Cake\I18n\Number;
use Cake\ORM\Query;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\MarksController Test Case
 *
 * @uses \App\Controller\MarksController
 */
class MarksControllerTest extends TestCase {
    use IntegrationTestTrait;
    use DependsOnFixtureTrait;
    use AuthenticateTrait;
    use ExperimentSiteTrait;
    use AjaxTrait;
    use MarksControllerTestTrait;

    private const ENDPOINT = '/marks';
    private const TABLE = 'Marks';
    private const CONTAINS = [
        'Batches',
        'Varieties',
        'Trees',
        'MarkValues',
        'MarkValues.MarkFormProperties',
    ];

    protected array $dependsOnFixture = [
        'Batches',
        'Varieties',
        'Trees',
        'MarkFormPropertyTypes',
        'MarkFormProperties',
        'Marks',
        'MarkValues',
    ];
    protected MarksTable $Table;

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
        $this->addEntity(true);

        $this->get( self::ENDPOINT );

        $this->assertResponseSuccess();
        $this->assertResponseCode( 200 );

        $query = $this->Table
            ->find()
            ->contain(self::CONTAINS)
            ->orderDesc( self::TABLE . '.modified' )
            ->limit( 100 )
            ->all();

        /** @var Mark $first */
        $first = $query->first();
        $last  = $query->last();

        $this->assertResponseContains( Number::format( $first->id ) );
        $this->assertResponseContains( $first->mark_values[0]->mark_form_property->name .': '.$first->mark_values[0]->value );

        $this->assertResponseContains( Number::format( $last->id ) );
        if ($last->mark_values){
            $this->assertResponseContains( $last->mark_values[0]->mark_form_property->name .': '.$last->mark_values[0]->value );
        }
    }

    /**
     * Test view method
     *
     * @return void
     */
    public function testView(): void {
        $entity = $this->addEntity(true);

        $this->get( self::ENDPOINT . "/view/{$entity->id}" );

        $this->assertResponseSuccess();
        $this->assertResponseCode( 200 );

        $this->assertResponseRegExp(
            '/<th[^>]*>Id<\/th>\s*<td[^>]*>\s*' . Number::format( $entity->id ) . '\s*<\/td>/'
        );
        foreach( $entity->mark_values as $mark_value ){
            $name = preg_quote($mark_value->mark_form_property->name, '/');
            $value = preg_quote($mark_value->value, '/');
            $this->assertResponseRegExp(
                '/<td[^>]*>\s*'.$mark_value->id.'\s*<\/td>\s*<td[^>]*>\s*'.$name.'\s*<\/td>\s*<td[^>]*>\s*'.$value.'\s*<\/td>/'
            );
        }
    }

    /**
     * Test addTreeMark method
     *
     * @return void
     */
    public function testAddTreeMark(): void {
        $data = $this->getNonExistingEntityData();
        $data = $this->appendMarkValues( $data );

        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->setUnlockedFields( [ 'tree_id' ] );

        $this->post( self::ENDPOINT . '/add-tree-mark', $data );

        $this->assertResponseSuccess();
        $this->assertEntityExists( $data );
    }

    /**
     * Test addVarietyMark method
     *
     * @return void
     */
    public function testAddVarietyMark(): void {
        $data = $this->getNonExistingEntityData();
        $data = $this->appendMarkValues( $data );

        unset( $data['tree_id'] );
        $variety = $this->getTable( 'Varieties' )
                        ->find()
                        ->firstOrFail();

        $data['variety_id'] = $variety->id;

        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->setUnlockedFields( [ 'variety_id' ] );

        $this->post( self::ENDPOINT . '/add-variety-mark', $data );

        $this->assertResponseSuccess();
        $this->assertEntityExists( $data );
    }

    /**
     * Test addBatchMark method
     *
     * @return void
     */
    public function testAddBatchMark(): void {
        $data = $this->getNonExistingEntityData();
        $data = $this->appendMarkValues( $data );

        unset( $data['tree_id'] );
        $batch = $this->getTable( 'Batches' )
                      ->find()
                      ->firstOrFail();

        $data['batch_id'] = $batch->id;

        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->setUnlockedFields( [ 'batch_id' ] );

        $this->post( self::ENDPOINT . '/add-batch-mark', $data );

        $this->assertResponseSuccess();
        $this->assertEntityExists( $data );
    }

    /**
     * Test edit method
     *
     * @return void
     */
    public function testEdit(): void {
        $entity = $this->addEntity();

        $data = [
            'date'         => '01.01.2021',
            'author'       => $entity->author,
            'mark_form_id' => $entity->mark_form_id,
            'tree_id'      => $entity->tree_id
        ];

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
     * Test getFormFields method
     *
     * @return void
     */
    public function testGetFormFields(): void {
        /** @var MarkForm $form */
        $form = $this->getTable( 'MarkForms' )
                     ->find()
                     ->contain( [
                         'MarkFormFields' => [
                             'joinType' => 'INNER'
                         ],
                         'MarkFormFields.MarkFormProperties'
                     ] )
                     ->firstOrFail();

        $this->get( self::ENDPOINT . '/get-form-fields/' . $form->id );

        $this->assertResponseSuccess();
        $this->assertResponseCode( 200 );

        foreach ( $form->mark_form_fields as $field ) {
            $property_name = preg_quote( $field->mark_form_property->name, '/' );
            $property_id   = $field->mark_form_property_id;
            $this->assertResponseRegExp(
                '/' .
                '<label[^>]+mark-form-fields-mark-form-properties-' . $property_id . '-mark-values-value[^>]+>\s*' . $property_name . '\s*<\/label>' .
                '.*<[^>]+name="mark_form_fields\[mark_form_properties\]\[' . $property_id . '\]\[mark_values\]\[value\]"[^>]*>' .
                '/'
            );
        }
    }

    /**
     * Test filter method
     *
     * @return void
     */
    public function testFilter(): void {
        $entity = $this->addEntity(true);

        $this->setAjaxHeader();
        $this->get( self::ENDPOINT . '/filter?fields%5B%5D=mark_form_property_type_id&term=' . $entity->mark_values[0]->mark_form_property_id );
        $this->assertResponseSuccess();
        $this->assertResponseContains( $entity->mark_values[0]->mark_form_property->name );
    }

    /**
     * Test filter method
     *
     * @return void
     */
    public function testFilter_nothing(): void {
        $this->setAjaxHeader();
        $this->get( self::ENDPOINT . '/filter?fields%5B%5D=mark_form_property_type_id&term=-1' );
        $this->assertResponseSuccess();
        $this->assertResponseContains( 'nothing_found' );
    }
}
