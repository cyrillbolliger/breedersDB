<?php
declare( strict_types=1 );

namespace App\Test\TestCase\Controller;

use App\Model\Entity\Batch;
use App\Model\Table\BatchesTable;
use App\Test\Util\AjaxTrait;
use App\Test\Util\AuthenticateTrait;
use App\Test\Util\DependsOnFixtureTrait;
use App\Test\Util\ExperimentSiteTrait;
use Cake\ORM\Query;
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
    use AjaxTrait;

    protected array $dependsOnFixture = [ 'Batches', 'Varieties' ];
    protected BatchesTable $Batches;

    protected function setUp(): void {
        $this->authenticate();
        $this->setSite();
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
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

        $query = $this->Batches
            ->find()
            ->where( [ 'Batches.id !=' => 1 ] )
            ->orderDesc( 'Batches.modified' )
            ->limit( 100 )
            ->all();

        /** @var Batch $first */
        $first = $query->first();
        $last  = $query->last();

        $this->assertResponseContains( $first->crossing_batch );
        $this->assertResponseContains( $last->crossing_batch );
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
        self::markTestIncomplete( 'Not implemented yet.' );
    }

    /**
     * Test add method
     *
     * @return void
     */
    public function testAdd(): void {
        $data = $this->getNonExistingBatchData();

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post( 'batches/add', $data );

        $this->assertResponseSuccess();
        $this->assertBatchExists( $data );

        $this->Batches->deleteManyOrFail( $this->getBatchQueryFromArray( $data ) );
    }

    /**
     * Test edit method
     *
     * @return void
     */
    public function testEdit(): void {
        $batch = $this->addBatch();

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
        $this->enableSecurityToken();

        $this->post( "batches/edit/{$batch->id}", $changed );

        $this->assertResponseSuccess();
        $this->assertBatchExists( $changed );

        $this->Batches->deleteManyOrFail( $this->getBatchQueryFromArray( $changed ) );
    }

    /**
     * Test delete method
     *
     * @return void
     */
    public function testDelete(): void {
        $batch = $this->addBatch();

        $this->enableCsrfToken();
        $this->enableSecurityToken();

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
    public function testFilter_crossingBatch(): void {
        $batch = $this->addBatch();

        $this->setAjaxHeader();
        $this->get( '/batches/filter?fields%5B%5D=crossing_batch&term=' . $batch->crossing_batch );
        $this->assertResponseSuccess();
        $this->assertResponseContains( $batch->crossing_batch );
    }

    /**
     * Test filter method
     *
     * @return void
     */
    public function testFilter_crossing(): void {
        $batch = $this->addBatch();

        $this->setAjaxHeader();
        $this->get( '/batches/filter?fields%5B%5D=crossing_batch&term=' . explode( '.', $batch->crossing_batch )[0] );
        $this->assertResponseSuccess();
        $this->assertResponseContains( $batch->crossing_batch );
    }

    /**
     * Test filter method
     *
     * @return void
     */
    public function testFilter_batch(): void {
        $batch = $this->addBatch();

        $this->setAjaxHeader();
        $this->get( '/batches/filter?fields%5B%5D=crossing_batch&term=.' . explode( '.', $batch->crossing_batch )[1] );
        $this->assertResponseSuccess();
        $this->assertResponseContains( $batch->crossing_batch );
    }

    /**
     * Test filter method
     *
     * @return void
     */
    public function testFilter_nothing(): void {
        $this->setAjaxHeader();
        $this->get( '/batches/filter?fields%5B%5D=crossing_batch&term=thisStringDoesNeverMatch' );
        $this->assertResponseSuccess();
        $this->assertResponseContains( 'nothing_found' );
    }

    /**
     * Test print method
     *
     * @return void
     */
    public function testPrint(): void {
        $batch = $this->addBatch();
        $this->get( "/batches/print/{$batch->id}/view/{$batch->id}" );
        $this->assertResponseSuccess();
        $this->assertResponseContains( 'print_button_regular' );
        $this->assertResponseContains( $batch->crossing_batch );
    }

    private function addBatch(): Batch {
        $data  = $this->getNonExistingBatchData();

        $data['date_sowed'] = date_create_from_format('d.m.Y', $data['date_sowed'])->format('Y-m-d');
        $data['date_planted'] = date_create_from_format('d.m.Y', $data['date_planted'])->format('Y-m-d');

        $batch = $this->Batches->newEntity( $data );

        $saved = $this->Batches->saveOrFail( $batch );

        return $this->Batches->get( $saved->id );
    }

    private function getNonExistingBatchData(): array {
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

        $query = $this->getBatchQueryFromArray( $data );
        $this->Batches->deleteManyOrFail( $query );

        return $data;
    }

    private function assertBatchExists( array $expected ): void {
        $query = $this->getBatchQueryFromArray( $expected );

        self::assertEquals( 1, $query->count() );

        /** @var Batch $dbData */
        $dbData = $query->first();
        self::assertEquals( $dbData->date_sowed, $expected['date_sowed'] );
        self::assertEquals( $dbData->numb_seeds_sowed, $expected['numb_seeds_sowed'] );
        self::assertEquals( $dbData->numb_sprouts_grown, $expected['numb_sprouts_grown'] );
        self::assertEquals( $dbData->seed_tray, $expected['seed_tray'] );
        self::assertEquals( $dbData->date_planted, $expected['date_planted'] );
        self::assertEquals( $dbData->numb_sprouts_planted, $expected['numb_sprouts_planted'] );
        self::assertEquals( $dbData->patch, $expected['patch'] );
        self::assertEquals( $dbData->note, $expected['note'] );
    }

    private function getBatchQueryFromArray( array $data ): Query {
        return $this->Batches->find()
                             ->where( [ 'crossing_id' => $data['crossing_id'] ] )
                             ->andWhere( [ 'code' => $data['code'] ] );
    }
}
