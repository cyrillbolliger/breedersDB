<?php
declare( strict_types=1 );

namespace App\Test\TestCase\Controller;

use App\Model\Entity\Mark;
use App\Model\Entity\MarkForm;
use App\Model\Entity\Tree;
use App\Model\Table\MarksTable;
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
        $this->assertResponseContains( $last->mark_values[0]->mark_form_property->name .': '.$last->mark_values[0]->value );
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

    private function addEntity( $withMarkValues = false ): Mark {
        $data   = $this->getNonExistingEntityData();
        $entity = $this->Table->newEntity( $data );

        if ( $withMarkValues ) {
            $ValuesTable = $this->getTable( 'MarkValues' );
            $data        = $this->appendMarkValues( $data );
            foreach ( $data['mark_form_fields']['mark_form_properties'] as $property_id => $value ) {
                $mark_values[] = $ValuesTable->newEntity( [
                    'value'                 => $value['mark_values']['value'],
                    'exceptional_mark'      => false,
                    'mark_form_property_id' => $property_id
                ] );
            }
            $entity->mark_values = $mark_values ?? [];
        }

        $saved = $this->Table->saveOrFail( $entity );

        return $this->Table->get( $saved->id, [
            'contain' => self::CONTAINS,
        ] );
    }

    private function getNonExistingEntityData(): array {
        /** @var MarkForm $form */
        $form = $this->getTable( 'MarkForms' )
                     ->find()
                     ->contain( [ 'MarkFormFields', 'MarkFormFields.MarkFormProperties' ] )
                     ->matching( 'MarkFormFields', function ( $q ) {
                         return $q->where( [ 'MarkFormFields.mark_form_id = MarkForms.id' ] );
                     } )
                     ->firstOrFail();

        /** @var Tree $tree */
        $tree = $this->getTable( 'Trees' )
                     ->find()
                     ->firstOrFail();

        $faker = \Faker\Factory::create();

        $data = [
            'date'         => '11.12.2020',
            'author'       => $faker->uuid, // misuse the author as unique identifier
            'mark_form_id' => $form->id,
            'tree_id'      => $tree->id
        ];

        $query = $this->getEntityQueryFromArray( $data );

        $this->deleteWithAssociatedData( $query );

        return $data;
    }

    private function appendMarkValues( array $data ): array {
        /** @var MarkForm $form */
        $form = $this->getTable( 'MarkForms' )
                     ->get(
                         $data['mark_form_id'],
                         [
                             'contain' => [
                                 'MarkFormFields',
                                 'MarkFormFields.MarkFormProperties'
                             ]
                         ] );

        $faker = \Faker\Factory::create();

        foreach ( $form->mark_form_fields as $field ) {
            $property = $field->mark_form_property;

            switch ( $property->field_type ) {
                case 'FLOAT':
                case 'INTEGER':
                    $value = $faker->numberBetween( $property->validation_rule['min'], $property->validation_rule['max'] );
                    break;
                case 'BOOLEAN':
                    $value = $faker->boolean;
                    break;
                case 'DATE':
                    $value = $faker->date( 'd.m.Y' );
                    break;
                default:
                    $value = $faker->sentence;
            }

            $data['mark_form_fields']['mark_form_properties'][ $property->id ]['mark_values']['value'] = $value;
        }

        return $data;
    }

    private function deleteWithAssociatedData( Query $query ): void {
        $markValuesTable = $this->getTable( 'MarkValues' );

        /** @var Mark $mark */
        foreach ( $query->all() as $mark ) {
            $markValuesTable->deleteManyOrFail( $mark->mark_values );
        }

        $this->Table->deleteManyOrFail( $query );
    }

    private function getEntityQueryFromArray( array $data ): Query {
        return $this->Table->find()
                           ->contain( self::CONTAINS )
                           ->where( [ self::TABLE . '.author' => $data['author'] ] );
    }

    private function assertEntityExists( array $expected ): void {
        $query = $this->getEntityQueryFromArray( $expected );

        self::assertEquals( 1, $query->count() );

        /** @var Mark $dbData */
        $dbData = $query->first();
        self::assertEquals( $dbData->date, $expected['date'] );
        self::assertEquals( $dbData->author, $expected['author'] );
        self::assertEquals( $dbData->mark_form_id, $expected['mark_form_id'] );

        foreach ( [ 'tree_id', 'variety_id', 'batch_id' ] as $type ) {
            if ( array_key_exists( $type, $expected ) ) {
                self::assertEquals( $expected[ $type ], $dbData->$type );
            } else {
                self::assertEmpty( $dbData->$type );
            }
        }

        if ( array_key_exists( 'mark_form_properties', $expected ) ) {
            foreach ( $expected['mark_form_properties'] as $property_id => $property ) {
                $expectedValue = $property['mark_values']['value'];
                $storedValue   = null;
                foreach ( $dbData->mark_values as $markValue ) {
                    if ( $markValue->id === $property_id ) {
                        $storedValue = $markValue->value;
                        break;
                    }
                }

                self::assertEquals( $expectedValue, $storedValue );
            }
        }
    }
}
