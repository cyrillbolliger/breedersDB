<?php
declare( strict_types=1 );

namespace App\Test\TestCase\Controller;

use App\Model\Entity\Crossing;
use App\Model\Entity\MotherTree;
use App\Model\Entity\Tree;
use App\Model\Table\MotherTreesTable;
use App\Test\Util\AjaxTrait;
use App\Test\Util\AuthenticateTrait;
use App\Test\Util\DependsOnFixtureTrait;
use App\Test\Util\ExperimentSiteTrait;
use Cake\ORM\Query;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\MotherTreesController Test Case
 *
 * @uses \App\Controller\MotherTreesController
 */
class MotherTreesControllerTest extends TestCase {
    use IntegrationTestTrait;
    use DependsOnFixtureTrait;
    use AuthenticateTrait;
    use ExperimentSiteTrait;
    use AjaxTrait;

    protected array $dependsOnFixture = [ 'Trees', 'Crossings' ];
    protected MotherTreesTable $MotherTrees;

    protected function setUp(): void {
        $this->authenticate();
        $this->setSite();
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->MotherTrees = $this->getTable( 'MotherTrees' );
        parent::setUp();
    }


    /**
     * Test index method
     *
     * @return void
     */
    public function testIndex(): void {
        $this->addMotherTree();

        $this->get( '/mother-trees' );

        $this->assertResponseSuccess();
        $this->assertResponseCode( 200 );

        $query = $this->MotherTrees
            ->find()
            ->orderDesc( 'MotherTrees.modified' )
            ->limit( 100 );

        /** @var MotherTree $first */
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
        $motherTree = $this->addMotherTree();

        $this->get( "/mother-trees/view/{$motherTree->id}" );

        $this->assertResponseSuccess();
        $this->assertResponseCode( 200 );

        $this->assertResponseContains( $motherTree->code );
        $this->assertResponseContains( $motherTree->tree->publicid );
        $this->assertResponseContains( $motherTree->crossing->code );
    }

    /**
     * Test add method
     *
     * @return void
     */
    public function testAdd(): void {
        $data = $this->getNonExistingMotherTreeData();

        $this->enableCsrfToken();

        $this->post( 'mother-trees/add', $data );

        $this->assertResponseSuccess();
        $this->assertMotherTreeExists( $data );

        $this->MotherTrees->deleteManyOrFail( $this->getMotherTreeQueryFromArray( $data ) );
    }

    /**
     * Test edit method
     *
     * @return void
     */
    public function testEdit(): void {
        $motherTree = $this->addMotherTree();

        $data = [
            'code'                  => 'TEST MOTHER TREE CHANGED',
            'planed'                => true,
            'date_pollen_harvested' => '03.02.2021',
            'date_impregnated'      => '03.02.2021',
            'date_fruit_harvested'  => '03.02.2021',
            'numb_portions'         => 101,
            'numb_flowers'          => 99,
            'numb_fruits'           => 88,
            'numb_seeds'            => 77,
            'note'                  => 'The best hugo on earth',
            'tree_id'               => $motherTree->tree_id,
            'crossing_id'           => $motherTree->crossing_id
        ];

        $this->enableCsrfToken();

        $this->post( "mother-trees/edit/{$motherTree->id}", $data );

        $this->assertResponseSuccess();
        $this->assertMotherTreeExists( $data );

        $this->MotherTrees->deleteManyOrFail( $this->getMotherTreeQueryFromArray( $data ) );
    }

    /**
     * Test delete method
     *
     * @return void
     */
    public function testDelete(): void {
        $motherTree = $this->addMotherTree();

        $this->enableCsrfToken();

        $this->delete( "mother-trees/delete/{$motherTree->id}" );
        $this->assertResponseSuccess();

        $query = $this->getMotherTreeQueryFromArray( $motherTree->toArray() );
        self::assertEquals( 0, $query->count() );
    }

    /**
     * Test filter method
     *
     * @return void
     */
    public function testFilter_code(): void {
        $motherTree = $this->addMotherTree();

        $this->setAjaxHeader();
        $this->get( '/mother-trees/filter?fields%5B%5D=code&fields%5B%5D=publicid&term=' . $motherTree->code );
        $this->assertResponseSuccess();
        $this->assertResponseContains( $motherTree->code );
    }

    /**
     * Test filter method
     *
     * @return void
     */
    public function testFilter_publicid(): void {
        $motherTree = $this->addMotherTree();

        $this->setAjaxHeader();
        $this->get( '/mother-trees/filter?fields%5B%5D=code&fields%5B%5D=publicid&term=' . $motherTree->tree->publicid );
        $this->assertResponseSuccess();
        $this->assertResponseContains( $motherTree->code );
    }

    /**
     * Test filter method
     *
     * @return void
     */
    public function testFilter_nothing(): void {
        $this->setAjaxHeader();
        $this->get( '/mother-trees/filter?fields%5B%5D=code&fields%5B%5D=publicid&term=thiscrossingdoesnotexist' );
        $this->assertResponseSuccess();
        $this->assertResponseContains( 'nothing_found' );
    }

    private function addMotherTree(): MotherTree {
        $data       = $this->getNonExistingMotherTreeData();
        $motherTree = $this->MotherTrees->newEntity( $data );

        $saved = $this->MotherTrees->saveOrFail( $motherTree );

        return $this->MotherTrees->get( $saved->id, [
            'contain' => [ 'Trees', 'Crossings' ]
        ] );
    }

    private function getNonExistingMotherTreeData(): array {
        /** @var Crossing $crossing */
        $crossing = $this->getTable( 'Crossings' )
                         ->find()
                         ->firstOrFail();

        /** @var Tree $tree */
        $tree = $this->getTable( 'Trees' )
                     ->find()
                     ->firstOrFail();

        $data = [
            'code'                  => 'TEST MOTHER TREE',
            'planed'                => false,
            'date_pollen_harvested' => '01.02.2021',
            'date_impregnated'      => '01.02.2021',
            'date_fruit_harvested'  => '01.02.2021',
            'numb_portions'         => 100,
            'numb_flowers'          => 90,
            'numb_fruits'           => 80,
            'numb_seeds'            => 70,
            'note'                  => 'The best variety on earth',
            'tree_id'               => $tree->id,
            'crossing_id'           => $crossing->id
        ];

        $query = $this->getMotherTreeQueryFromArray( $data );

        $this->MotherTrees->deleteManyOrFail( $query );

        return $data;
    }

    private function assertMotherTreeExists( array $expected ): void {
        $query = $this->getMotherTreeQueryFromArray( $expected );

        self::assertEquals( 1, $query->count() );

        /** @var MotherTree $dbData */
        $dbData = $query->first();
        self::assertEquals( $dbData->code, $expected['code'] );
        self::assertEquals( $dbData->planed, $expected['planed'] );
        self::assertEquals( $dbData->date_pollen_harvested->format('d.m.Y'), $expected['date_pollen_harvested'] );
        self::assertEquals( $dbData->date_impregnated->format('d.m.Y'), $expected['date_impregnated'] );
        self::assertEquals( $dbData->date_fruit_harvested->format('d.m.Y'), $expected['date_fruit_harvested'] );
        self::assertEquals( $dbData->numb_portions, $expected['numb_portions'] );
        self::assertEquals( $dbData->numb_flowers, $expected['numb_flowers'] );
        self::assertEquals( $dbData->numb_fruits, $expected['numb_fruits'] );
        self::assertEquals( $dbData->numb_seeds, $expected['numb_seeds'] );
        self::assertEquals( $dbData->note, $expected['note'] );
        self::assertEquals( $dbData->tree_id, $expected['tree_id'] );
        self::assertEquals( $dbData->crossing_id, $expected['crossing_id'] );
    }


    private function getMotherTreeQueryFromArray( array $data ): Query {
        return $this->MotherTrees->find()
                                 ->contain( [ 'Trees', 'Crossings' ] )
                                 ->where( [ 'MotherTrees.code' => $data['code'] ] );
    }
}
