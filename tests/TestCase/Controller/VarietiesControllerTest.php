<?php
declare( strict_types=1 );

namespace App\Test\TestCase\Controller;

use App\Model\Entity\Batch;
use App\Model\Entity\Variety;
use App\Model\Table\VarietiesTable;
use App\Test\Util\AjaxTrait;
use App\Test\Util\AuthenticateTrait;
use App\Test\Util\DependsOnFixtureTrait;
use App\Test\Util\ExperimentSiteTrait;
use Cake\ORM\Query;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\VarietiesController Test Case
 *
 * @uses \App\Controller\VarietiesController
 */
class VarietiesControllerTest extends TestCase {
    use IntegrationTestTrait;
    use DependsOnFixtureTrait;
    use AuthenticateTrait;
    use ExperimentSiteTrait;
    use AjaxTrait;

    protected array $dependsOnFixture = [ 'Crossings', 'Batches' ];
    protected VarietiesTable $Varieties;

    protected function setUp(): void {
        $this->authenticate();
        $this->setSite();
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->Varieties = $this->getTable( 'Varieties' );
        parent::setUp();

        $this->setUnlockedFields( [ 'code', 'batch_id' ] );
    }

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndex(): void {
        $this->addVariety();

        $this->get( '/varieties' );

        $this->assertResponseSuccess();
        $this->assertResponseCode( 200 );

        $query = $this->Varieties
            ->find()
            ->orderDesc( 'Varieties.modified' )
            ->limit( 100 )
            ->all();

        /** @var Variety $first */
        $first = $query->first();
        $last  = $query->last();

        $this->assertResponseContains( $first->convar );
        $this->assertResponseContains( $last->convar );
    }

    /**
     * Test view method
     *
     * @return void
     */
    public function testView(): void {
        $variety = $this->addVariety();

        $this->get( "/varieties/view/{$variety->id}" );

        $this->assertResponseSuccess();
        $this->assertResponseCode( 200 );

        $this->assertResponseContains( $variety->convar );
        $this->assertResponseRegExp( "/{$variety->batch->crossing_batch}(?!\.{$variety->code})/" );

        // todo: related trees, related scions bundles, related marks
        self::markTestIncomplete( 'Not implemented yet.' );
    }

    /**
     * Test addBreederVariety method
     *
     * @return void
     */
    public function testAddBreederVariety(): void {
        $data = $this->getNonExistingVarietyData();

        unset(
            $data['official_name'],
            $data['acronym'],
            $data['plant_breeder'],
            $data['registration']
        );

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post( 'varieties/add-breeder-variety', $data );

        $this->assertResponseSuccess();
        $this->assertVarietyExists( $data );

        $this->Varieties->deleteManyOrFail( $this->getVarietyQueryFromArray( $data ) );
    }

    /**
     * Test addOfficialVariety method
     *
     * @return void
     */
    public function testAddOfficialVariety(): void {
        $data             = $this->getNonExistingVarietyData();
        $data['batch_id'] = 1;

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post( 'varieties/add-official-variety', $data );

        $this->assertResponseSuccess();
        $this->assertVarietyExists( $data );

        $this->Varieties->deleteManyOrFail( $this->getVarietyQueryFromArray( $data ) );
    }

    /**
     * Test edit method
     *
     * @return void
     */
    public function testEdit(): void {
        $variety = $this->addVariety();

        $data = [
            'code'          => 'changed',
            'official_name' => 'changed',
            'acronym'       => 'swap',
            'plant_breeder' => 'changed',
            'registration'  => 'changed',
            'description'   => 'changed',
            'batch_id'      => $variety->batch_id,
        ];

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post( "varieties/edit/{$variety->id}", $data );

        $this->assertResponseSuccess();
        $this->assertVarietyExists( $data );

        $this->Varieties->deleteManyOrFail( $this->getVarietyQueryFromArray( $data ) );
    }

    /**
     * Test delete method
     *
     * @return void
     */
    public function testDelete(): void {
        $variety = $this->addVariety();

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->delete( "varieties/delete/{$variety->id}" );
        $this->assertResponseSuccess();

        $query = $this->getVarietyQueryFromArray( $variety->toArray() );
        self::assertEquals( 0, $query->count() );
    }

    /**
     * Test searchCrossingBatchs method
     *
     * @return void
     */
    public function testSearchCrossingBatchs(): void {
        $variety = $this->addVariety();

        $this->setAjaxHeader();
        $this->get( '/varieties/searchCrossingBatchs?term=' . $variety->batch->crossing_batch );
        $this->assertResponseSuccess();
        $this->assertResponseContains( $variety->batch->crossing_batch );
    }

    /**
     * Test searchConvars method
     *
     * @return void
     */
    public function testSearchConvars(): void {
        $variety = $this->addVariety();

        $this->setAjaxHeader();
        $this->get( '/varieties/searchConvars?term=' . $variety->convar );
        $this->assertResponseSuccess();
        $this->assertResponseContains( $variety->convar );
    }

    /**
     * Test getNextFreeCode method
     *
     * @return void
     */
    public function testGetNextFreeCode(): void {
        $variety = $this->addVariety();

        $greatestCodeVariety = $this->Varieties->find()
                                               ->where( [ 'batch_id' => $variety->batch_id ] )
                                               ->order( [ 'code' => 'DESC' ] )
                                               ->first();

        $expectedCode = (string) sprintf( '%03d', (int) $greatestCodeVariety->code + 1 );

        $this->setAjaxHeader();
        $this->get( '/varieties/getNextFreeCode?batch_id=' . $variety->batch_id );
        $this->assertResponseSuccess();
        $this->assertResponseContains( $expectedCode );
    }

    /**
     * Test filter method
     *
     * @return void
     */
    public function testFilter(): void {
        $variety = $this->addVariety();

        $this->setAjaxHeader();
        $this->get( '/varieties/filter?fields%5B%5D=convar&fields%5B%5D=breeder_variety_code&fields%5B%5D=id&term=' . $variety->id );
        $this->assertResponseSuccess();
        $this->assertResponseContains( $variety->convar );

        $this->setAjaxHeader();
        $this->get( '/varieties/filter?fields%5B%5D=convar&fields%5B%5D=breeder_variety_code&fields%5B%5D=id&term=' . $variety->convar );
        $this->assertResponseSuccess();
        $this->assertResponseContains( $variety->convar );

        $this->setAjaxHeader();
        $this->get( '/varieties/filter?fields%5B%5D=convar&fields%5B%5D=breeder_variety_code&fields%5B%5D=id&term=' . COMPANY_ABBREV . $variety->id );
        $this->assertResponseSuccess();
        $this->assertResponseContains( $variety->convar );
    }

    private function addVariety(): Variety {
        $data    = $this->getNonExistingVarietyData();
        $variety = $this->Varieties->newEntity( $data );

        $saved = $this->Varieties->saveOrFail( $variety );

        return $this->Varieties->get( $saved->id, [
            'contain' => [ 'Batches' ]
        ] );
    }

    private function getNonExistingVarietyData(): array {
        /** @var Batch $batch */
        $batch = $this->getTable( 'Batches' )
                      ->find()
                      ->firstOrFail();

        $data = [
            'code'          => '999',
            'official_name' => 'supervariety',
            'acronym'       => 'supi',
            'plant_breeder' => 'Hugo',
            'registration'  => 'worldwide',
            'description'   => 'this variety is so super',
            'batch_id'      => $batch->id,
        ];

        $query = $this->getVarietyQueryFromArray( $data );
        $this->Varieties->deleteManyOrFail( $query );

        return $data;
    }

    private function getVarietyQueryFromArray( array $data ): Query {
        return $this->Varieties->find()
                               ->contain( [ 'Batches' ] )
                               ->where( [
                                   'Varieties.code'     => $data['code'],
                                   'Varieties.batch_id' => $data['batch_id']
                               ] );
    }

    private function assertVarietyExists( array $expected ): void {
        $query = $this->getVarietyQueryFromArray( $expected );

        self::assertEquals( 1, $query->count() );

        /** @var Variety $dbData */
        $dbData = $query->firstOrFail();
        self::assertEquals( $dbData->code, $expected['code'] );
        self::assertEquals( $dbData->description, $expected['description'] );
        self::assertEquals( $dbData->batch_id, $expected['batch_id'] );

        if ( array_key_exists( 'official_name', $expected ) ) {
            self::assertEquals( $dbData->official_name, $expected['official_name'] );
        }
        if ( array_key_exists( 'acronym', $expected ) ) {
            self::assertEquals( $dbData->acronym, $expected['acronym'] );
        }
        if ( array_key_exists( 'plant_breeder', $expected ) ) {
            self::assertEquals( $dbData->plant_breeder, $expected['plant_breeder'] );
        }
        if ( array_key_exists( 'registration', $expected ) ) {
            self::assertEquals( $dbData->registration, $expected['registration'] );
        }
    }
}
