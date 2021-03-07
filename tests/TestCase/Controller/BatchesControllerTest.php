<?php
declare( strict_types=1 );

namespace App\Test\TestCase\Controller;

use App\Test\Fixture\AuthenticateTrait;
use App\Test\Fixture\DependsOnFixtureTrait;
use App\Test\Fixture\ExperimentSiteTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\BatchesController Test Case
 *
 * @uses \App\Controller\BatchesController
 */
class BatchesControllerTest extends TestCase {
    use IntegrationTestTrait;
    use DependsOnFixtureTrait;
    use AuthenticateTrait;
    use ExperimentSiteTrait;

    protected array $dependsOnFixture = [ 'Batches', 'Varieties' ];

    protected function setUp(): void {
        $this->authenticate();
        $this->setSite();
        parent::setUp();
    }


    /**
     * Test index method
     *
     * @return void
     */
    public function testIndex(): void {
        $this->get( '/batches' );

        $this->assertResponseSuccess();
        $this->assertResponseCode( 200 );

        $batches = $this->getTable( 'Batches' );
        $query   = $batches
            ->find()
            ->where( [ 'Batches.id !=' => 1 ] )
            ->orderDesc( 'Batches.modified' )
            ->limit( 100 );

        $this->assertResponseContains( $query->first()->crossing_batch );
        $this->assertResponseContains( $query->last()->crossing_batch );
    }

    /**
     * Test view method
     *
     * @return void
     */
    public function testView(): void {
        $batches = $this->getTable( 'Batches' );
        $batch   = $batches
            ->find()
            ->contain( [ 'Varieties' ] )
            ->where( [ 'Batches.id !=' => 1 ] )
            ->matching( 'Varieties', function ( $q ) {
                return $q->where( [ 'Varieties.id >' => 0 ] );
            } )
            ->firstOrFail();

        $this->get( "/batches/view/{$batch->id}" );

        $this->assertResponseSuccess();
        $this->assertResponseCode( 200 );

        $this->assertResponseContains( $batch->crossing_batch );
        $this->assertResponseContains( $batch->varieties[0]->convar );

        // todo: test marks
        $this->markTestIncomplete( 'Not implemented yet.' );
    }

    /**
     * Test add method
     *
     * @return void
     */
    public function testAdd(): void {
        $crossing = $this->getTable( 'Crossings' )
                         ->find()
                         ->firstOrFail();

        $data = [
            'crossing_id'          => $crossing->id,
            'code'                 => '99A',
            'date_sowed'           => '01.03.2021',
            'numb_seeds_sowed'     => 123,
            'numb_sprouts_grown'   => 5,
            'seed_tray'            => 1,
            'date_planted'         => '02.03.2021',
            'numb_sprouts_planted' => 4,
            'patch'                => 'The new patch',
            'note'                 => 'This is very important',
        ];

        $batches = $this->getTable( 'Batches' );
        $query   = $batches->find()
                           ->where( [ 'crossing_id' => $data['crossing_id'] ] )
                           ->andWhere( [ 'code' => $data['code'] ] );
        $batches->deleteMany( $query );

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post( 'batches/add', $data );

        $this->assertResponseSuccess();
        $query = $batches->find()
                         ->where( [ 'crossing_id' => $data['crossing_id'] ] )
                         ->andWhere( [ 'code' => $data['code'] ] );

        self::assertEquals( 1, $query->count() );

        $batches->deleteMany( $query );
    }

    /**
     * Test edit method
     *
     * @return void
     */
    public function testEdit(): void {
        $this->markTestIncomplete( 'Not implemented yet.' );
    }

    /**
     * Test delete method
     *
     * @return void
     */
    public function testDelete(): void {
        $this->markTestIncomplete( 'Not implemented yet.' );
    }

    /**
     * Test filter method
     *
     * @return void
     */
    public function testFilter(): void {
        $this->markTestIncomplete( 'Not implemented yet.' );
    }

    /**
     * Test print method
     *
     * @return void
     */
    public function testPrint(): void {
        $this->markTestIncomplete( 'Not implemented yet.' );
    }
}
