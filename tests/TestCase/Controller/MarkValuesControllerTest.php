<?php
declare( strict_types=1 );

namespace App\Test\TestCase\Controller;

use App\Controller\MarkValuesController;
use App\Model\Entity\Mark;
use App\Model\Entity\MarkFormProperty;
use App\Model\Entity\MarkValue;
use App\Model\Entity\MarkValueType;
use App\Model\Table\MarkValuesTable;
use App\Test\Util\AjaxTrait;
use App\Test\Util\AuthenticateTrait;
use App\Test\Util\DependsOnFixtureTrait;
use App\Test\Util\ExperimentSiteTrait;
use Cake\ORM\Query;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\MarkValuesController Test Case
 *
 * @uses \App\Controller\MarkValuesController
 */
class MarkValuesControllerTest extends TestCase {
    use IntegrationTestTrait;
    use DependsOnFixtureTrait;
    use AuthenticateTrait;
    use ExperimentSiteTrait;
    use AjaxTrait;

    private const ENDPOINT = '/mark-values';
    private const TABLE = 'MarkValues';
    private const CONTAINS = [
        'MarkFormProperties',
        'Marks',
    ];

    protected array $dependsOnFixture = [ 'MarkFormPropertyTypes', 'MarkForms' ] + self::CONTAINS + [ self::TABLE ];
    protected MarkValuesTable $Table;

    protected function setUp(): void {
        $this->authenticate();
        $this->setSite();
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->Table = $this->getTable( self::TABLE );
        parent::setUp();
    }

    /**
     * Test edit method
     *
     * @return void
     */
    public function testEdit(): void {
        $entity = $this->addEntity();

        $data = [
            'value'                 => (\Faker\Factory::create())->uuid,
            'exceptional_mark'      => false,
            'mark_form_property_id' => $entity->mark_form_property_id,
            'mark_id'               => $entity->mark_id
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

    private function addEntity(): MarkValue {
        $data   = $this->getNonExistingEntityData();
        $entity = $this->Table->newEntity( $data );

        $saved = $this->Table->saveOrFail( $entity );

        return $this->Table->get( $saved->id, [
            'contain' => self::CONTAINS,
        ] );
    }

    private function getNonExistingEntityData(): array {
        /** @var MarkFormProperty $property */
        $property = $this->getTable( 'MarkFormProperties' )
                         ->find()
                         ->where( [ 'field_type' => 'VARCHAR' ] )
                         ->firstOrFail();

        /** @var Mark $mark */
        $mark = $this->getTable( 'Marks' )
                     ->find()
                     ->firstOrFail();

        $data = [
            'value'                 => (\Faker\Factory::create())->uuid,
            'exceptional_mark'      => true,
            'mark_form_property_id' => $property->id,
            'mark_id'               => $mark->id
        ];

        $query = $this->getEntityQueryFromArray( $data );

        $this->Table->deleteManyOrFail($query);

        return $data;
    }

    private function getEntityQueryFromArray( array $data ): Query {
        return $this->Table->find()
                           ->contain( self::CONTAINS )
                           ->where( [ self::TABLE . '.value' => $data['value'] ] );
    }

    private function assertEntityExists( array $expected ): void {
        $query = $this->getEntityQueryFromArray( $expected );

        self::assertEquals( 1, $query->count() );

        /** @var MarkValue $dbData */
        $dbData = $query->first();
        self::assertEquals( $dbData->value, $expected['value'] );
        self::assertEquals( $dbData->exceptional_mark, $expected['exceptional_mark'] );
        self::assertEquals( $dbData->mark_form_property_id, $expected['mark_form_property_id'] );
        self::assertEquals( $dbData->mark_id, $expected['mark_id'] );
    }
}
