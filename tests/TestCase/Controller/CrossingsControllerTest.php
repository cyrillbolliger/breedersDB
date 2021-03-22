<?php
declare( strict_types=1 );

namespace App\Test\TestCase\Controller;

use App\Controller\CrossingsController;
use App\Model\Entity\Batch;
use App\Model\Entity\Crossing;
use App\Model\Entity\MotherTree;
use App\Model\Entity\Tree;
use App\Model\Table\CrossingsTable;
use App\Test\Util\AjaxTrait;
use App\Test\Util\AuthenticateTrait;
use App\Test\Util\DependsOnFixtureTrait;
use App\Test\Util\ExperimentSiteTrait;
use Cake\ORM\Query;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\CrossingsController Test Case
 *
 * @uses \App\Controller\CrossingsController
 */
class CrossingsControllerTest extends TestCase {
    use IntegrationTestTrait;
    use DependsOnFixtureTrait;
    use AuthenticateTrait;
    use ExperimentSiteTrait;
    use AjaxTrait;

    protected array $dependsOnFixture = [ 'Trees', 'MotherTrees', 'Batches', 'Varieties' ];
    protected CrossingsTable $Crossings;

    protected function setUp(): void {
        $this->authenticate();
        $this->setSite();
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->Crossings = $this->getTable( 'Crossings' );
        parent::setUp();
    }


    /**
     * Test index method
     *
     * @return void
     */
    public function testIndex(): void {
        $this->addCrossing();

        $this->get( '/crossings' );

        $this->assertResponseSuccess();
        $this->assertResponseCode( 200 );

        $query = $this->Crossings
            ->find()
            ->orderDesc( 'Crossings.modified' )
            ->limit( 100 );

        /** @var Crossing $first */
        $first = $query->firstOrFail();
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
        $crossing = $this->addCrossing();

        $batchesTable = $this->getTable( 'Batches' );
        $batchesTable->deleteManyOrFail(
            $batchesTable->find()
                         ->where( [
                             'code'        => '99Z',
                             'crossing_id' => $crossing->id
                         ] )
        );
        $batch              = new Batch();
        $batch->code        = '99Z';
        $batch->crossing_id = $crossing->id;
        $batchesTable->save( $batch );

        $motherTreesTable = $this->getTable( 'MotherTrees' );
        $motherTreesTable->deleteManyOrFail(
            $motherTreesTable->find()
                             ->where( [
                                 'code'        => 'TEST_TEST_99Z',
                                 'crossing_id' => $crossing->id
                             ] )
        );

        $motherTree              = new MotherTree();
        $motherTree->code        = 'TEST_TEST_99Z';
        $motherTree->crossing_id = $crossing->id;
        $motherTreesTable->save( $motherTree );

        $this->get( "/crossings/view/{$crossing->id}" );

        $this->assertResponseSuccess();
        $this->assertResponseCode( 200 );

        $this->assertResponseContains( $batch->code );
        $this->assertResponseContains( $motherTree->code );
    }

    /**
     * Test add method
     *
     * @return void
     */
    public function testAdd(): void {
        $data = $this->getNonExistingCrossingData();

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post( 'crossings/add', $data );

        $this->assertResponseSuccess();
        $this->assertCrossingExists( $data );

        $this->Crossings->deleteManyOrFail( $this->getCrossingQueryFromArray( $data ) );
    }

    /**
     * Test edit method
     *
     * @return void
     */
    public function testEdit(): void {
        $crossing = $this->addCrossing();

        $changed = [
            'code'              => 'CHANGED',
            'mother_variety_id' => $crossing->father_variety_id, // just swap mother and father
            'father_variety_id' => $crossing->mother_variety_id,
            'target'            => 'changed'
        ];

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post( "crossings/edit/{$crossing->id}", $changed );

        $this->assertResponseSuccess();
        $this->assertCrossingExists( $changed );

        $this->Crossings->deleteManyOrFail( $this->getCrossingQueryFromArray( $changed ) );
    }

    /**
     * Test delete method
     *
     * @return void
     */
    public function testDelete(): void {
        $crossing = $this->addCrossing();

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->delete( "crossings/delete/{$crossing->id}" );
        $this->assertResponseSuccess();

        $query = $this->getCrossingQueryFromArray( $crossing->toArray() );
        self::assertEquals( 0, $query->count() );
    }

    /**
     * Test filter method
     *
     * @return void
     */
    public function testFilter_crossing(): void {
        $crossing = $this->addCrossing();

        $this->setAjaxHeader();
        $this->get( '/crossings/filter?fields%5B%5D=code&term=' . $crossing->code );
        $this->assertResponseSuccess();
        $this->assertResponseContains( $crossing->code );
    }

    /**
     * Test filter method
     *
     * @return void
     */
    public function testFilter_crossing_partial(): void {
        $crossing = $this->addCrossing();

        $this->setAjaxHeader();
        $this->get( '/crossings/filter?fields%5B%5D=code&term=' . substr( $crossing->code, 0, 5 ) );
        $this->assertResponseSuccess();
        $this->assertResponseContains( $crossing->code );
    }

    /**
     * Test filter method
     *
     * @return void
     */
    public function testFilter_nothing(): void {
        $this->setAjaxHeader();
        $this->get( '/crossings/filter?fields%5B%5D=code&term=thiscrossingdoesnotexist' );
        $this->assertResponseSuccess();
        $this->assertResponseContains( 'nothing_found' );
    }

    private function addCrossing(): Crossing {
        $data     = $this->getNonExistingCrossingData();
        $crossing = $this->Crossings->newEntity( $data );

        $saved = $this->Crossings->saveOrFail( $crossing );

        return $this->Crossings->get( $saved->id, [
            'contain' => [ 'Batches', 'MotherTrees', 'Varieties' ]
        ] );
    }

    private function getNonExistingCrossingData(): array {
        /** @var MotherTree $motherTree */
        $motherTree = $this->getTable( 'MotherTrees' )
                           ->find()
                           ->contain( 'Trees' )
                           ->firstOrFail();

        /** @var Tree $fatherTree */
        $fatherTree = $this->getTable( 'Trees' )
                           ->find()
                           ->firstOrFail();

        $data = [
            'code'              => 'TEST1',
            'mother_variety_id' => $motherTree->tree->variety_id,
            'father_variety_id' => $fatherTree->variety_id,
            'target'            => 'The best variety on earth',
        ];

        $query = $this->getCrossingQueryFromArray( $data );

        foreach ( $query as $crossing ) {
            if ( $crossing->batches ) {
                $this->getTable( 'Batches' )->deleteManyOrFail( $crossing->batches );
            }
            if ( $crossing->batches ) {
                $this->getTable( 'MotherTrees' )->deleteManyOrFail( $crossing->mother_trees );
            }
        }

        $this->Crossings->deleteManyOrFail( $query );

        return $data;
    }

    private function assertCrossingExists( array $expected ): void {
        $query = $this->getCrossingQueryFromArray( $expected );

        self::assertEquals( 1, $query->count() );

        /** @var Crossing $dbData */
        $dbData = $query->first();
        self::assertEquals( $dbData->mother_variety_id, $expected['mother_variety_id'] );
        self::assertEquals( $dbData->father_variety_id, $expected['father_variety_id'] );
        self::assertEquals( $dbData->target, $expected['target'] );
    }

    private function getCrossingQueryFromArray( array $data ): Query {
        return $this->Crossings->find()
                               ->contain( [ 'Batches', 'MotherTrees' ] )
                               ->where( [ 'code' => $data['code'] ] );
    }
}
