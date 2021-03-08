<?php
declare( strict_types=1 );

namespace App\Test\TestCase\Controller;

use App\Test\Fixture\AuthenticateTrait;
use App\Test\Fixture\DependsOnFixtureTrait;
use App\Test\Fixture\ExperimentSiteTrait;
use Cake\Datasource\EntityInterface;
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
        $this->Batches = $this->getTable( 'Batches' );
        parent::setUp();
    }


    /**
     * Test index method
     *
     * @return void
     */
    public function testIndex(): void {
        $this->addBatch();

        $this->get( '/batches' );

        $this->assertResponseSuccess();
        $this->assertResponseCode( 200 );

        $query   = $this->Batches
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
        $batch = $this->addBatch();

        $this->get( "/batches/view/{$batch->id}" );

        $this->assertResponseSuccess();
        $this->assertResponseCode( 200 );

        $this->assertResponseContains( $batch->crossing_batch );

        // todo: test varieties
        // todo: test marks
        $this->markTestIncomplete( 'Not implemented yet.' );
    }

    /**
     * Test add method
     *
     * @return void
     */
    public function testAdd(): void {
        $data    = $this->getNonExistingBatchData();

        $this->enableCsrfToken();

        $this->post( 'batches/add', $data );

        $this->assertResponseSuccess();
        $this->assertBatchExists( $data );

        $this->Batches->deleteMany( $this->getBatchQueryFromArray( $data ) );
    }

    /**
     * Test edit method
     *
     * @return void
     */
    public function testEdit(): void {
        $batch   = $this->addBatch();

        $changed = [
            'crossing_id'          => $batch->crossing_id,
            'code'                 => '99Z',
            'date_sowed'           => '05.03.2021',
            'numb_seeds_sowed'     => 3,
            'numb_sprouts_grown'   => 6,
            'seed_tray'            => '21',
            'date_planted'         => '02.07.2021',
            'numb_sprouts_planted' => 5,
            'patch'                => 'The newest patch',
            'note'                 => 'This is not very important',
        ];

        $this->enableCsrfToken();

        $this->post( "batches/edit/{$batch->id}", $changed );

        $this->assertResponseSuccess();
        $this->assertBatchExists( $changed );

        $this->Batches->deleteMany( $this->getBatchQueryFromArray( $changed ) );
    }

    /**
     * Test delete method
     *
     * @return void
     */
    public function testDelete(): void {
        $batch = $this->addBatch();

        $this->enableCsrfToken();

        $this->delete( "batches/delete/{$batch->id}" );
        $this->assertResponseSuccess();

        $query = $this->getBatchQueryFromArray( $batch->toArray() );
        self::assertEquals( 0, $query->count() );
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

    private function addBatch(): EntityInterface {
        $data    = $this->getNonExistingBatchData();
        $batch   = $this->Batches->newEntity( $data );

        $saved = $this->Batches->save( $batch );

        return $this->Batches->get( $saved->id );
    }

    private function getNonExistingBatchData() {
        $crossing = $this->getTable( 'Crossings' )
                         ->find()
                         ->firstOrFail();

        $data = [
            'crossing_id'          => $crossing->id,
            'code'                 => '99A',
            'date_sowed'           => '01.03.2021',
            'numb_seeds_sowed'     => 123,
            'numb_sprouts_grown'   => 5,
            'seed_tray'            => '1',
            'date_planted'         => '02.03.2021',
            'numb_sprouts_planted' => 4,
            'patch'                => 'The new patch',
            'note'                 => 'This is very important',
        ];

        $query   = $this->Batches->find()
                           ->where( [ 'crossing_id' => $data['crossing_id'] ] )
                           ->andWhere( [ 'code' => $data['code'] ] );
        $this->Batches->deleteMany( $query );

        return $data;
    }

    private function assertBatchExists( array $expected ) {
        $query = $this->getBatchQueryFromArray( $expected );

        self::assertEquals( 1, $query->count() );

        $dbdata = $query->first();
        self::assertEquals( 1, $query->count() );
        self::assertEquals( $dbdata->date_sowed, $expected['date_sowed'] );
        self::assertEquals( $dbdata->numb_seeds_sowed, $expected['numb_seeds_sowed'] );
        self::assertEquals( $dbdata->numb_sprouts_grown, $expected['numb_sprouts_grown'] );
        self::assertEquals( $dbdata->seed_tray, $expected['seed_tray'] );
        self::assertEquals( $dbdata->date_planted, $expected['date_planted'] );
        self::assertEquals( $dbdata->numb_sprouts_planted, $expected['numb_sprouts_planted'] );
        self::assertEquals( $dbdata->patch, $expected['patch'] );
        self::assertEquals( $dbdata->note, $expected['note'] );
    }

    private function getBatchQueryFromArray( array $data ) {
        return $this->Batches->find()
                       ->where( [ 'crossing_id' => $data['crossing_id'] ] )
                       ->andWhere( [ 'code' => $data['code'] ] );
    }
}
