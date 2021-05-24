<?php
declare( strict_types=1 );

namespace App\Test\TestCase\Controller;

use App\Controller\QueriesController;
use App\Model\Entity\ExperimentSite;
use App\Model\Entity\Query;
use App\Model\Entity\QueryGroup;
use App\Model\Table\QueriesTable;
use App\Test\Util\AjaxTrait;
use App\Test\Util\AuthenticateTrait;
use App\Test\Util\DependsOnFixtureTrait;
use App\Test\Util\ExperimentSiteTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\QueriesController Test Case
 *
 * @uses \App\Controller\QueriesController
 */
class QueriesControllerTest extends TestCase {
    use IntegrationTestTrait;
    use DependsOnFixtureTrait;
    use AuthenticateTrait;
    use ExperimentSiteTrait;
    use AjaxTrait;

    private const ENDPOINT = '/queries';
    private const TABLE = 'Queries';
    private const CONTAINS = [
    ];

    protected array $dependsOnFixture = [
        'ExperimentSites',
        'QueryGroups',
        'Queries',
    ];
    protected QueriesTable $Table;

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
            ->all();

        /** @var Query $first */
        $first = $query->first();
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
        $entity = $this->addEntity();

        $this->get( self::ENDPOINT . "/view/{$entity->id}" );

        $this->assertResponseSuccess();
        $this->assertResponseCode( 200 );

        $this->markTestIncomplete();
    }

    /**
     * Test viewMarkQuery method
     *
     * @return void
     */
    public function testViewMarkQuery(): void {
        $this->markTestIncomplete( 'Not implemented yet.' );
    }

    /**
     * Test export method
     *
     * @return void
     */
    public function testExport(): void {
        $this->markTestIncomplete( 'Not implemented yet.' );
    }

    /**
     * Test add method
     *
     * @return void
     */
    public function testAdd(): void {
        $this->markTestIncomplete( 'Not implemented yet.' );
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

    private function addEntity(): Query {
        $data   = $this->getNonExistingEntityData();
        $entity = $this->Table->newEntity( $data );

        $saved = $this->Table->saveOrFail( $entity );

        return $this->Table->get( $saved->id );
    }

    private function getNonExistingEntityData(): array {
        /** @var QueryGroup $group */
        $group = $this->getTable( 'QueryGroups' )
                      ->find()
                      ->firstOrFail();

        /** @var ExperimentSite $experimentSite */
        $experimentSite = \Cake\ORM\TableRegistry::getTableLocator()
                                                 ->get('ExperimentSites')
                                                 ->find()
                                                 ->firstOrFail();

        $faker = \Faker\Factory::create();

        $data = [
            'code'           => $faker->uuid, // misuse the code as unique identifier
            'my_query'       => '{"root_view":"TreesView","fields":{"BatchesView":{"id":"0","crossing_batch":"0","date_sowed":"0","numb_seeds_sowed":"0","numb_sprouts_grown":"0","seed_tray":"0","date_planted":"0","numb_sprouts_planted":"0","patch":"0","note":"0","crossing_id":"0"},"CrossingsView":{"id":"0","code":"0","mother_variety":"0","father_variety":"0","target":"0"},"MarksView":{"id":"0","date":"0","author":"0","tree_id":"0","variety_id":"0","batch_id":"0","value":"0","exceptional_mark":"0","name":"0","property_id":"0","field_type":"0","property_type":"0"},"MotherTreesView":{"id":"0","crossing":"0","code":"0","planed":"0","date_pollen_harvested":"0","date_impregnated":"0","date_fruit_harvested":"0","numb_portions":"0","numb_flowers":"0","numb_fruits":"0","numb_seeds":"0","note":"0","publicid":"0","convar":"0","offset":"0","row":"0","experiment_site":"0","tree_id":"0","crossing_id":"0"},"ScionsBundlesView":{"id":"0","identification":"0","convar":"0","numb_scions":"0","date_scions_harvest":"0","descents_publicid_list":"0","note":"0","external_use":"0","variety_id":"0"},"TreesView":{"id":"0","publicid":"1","convar":"1","date_grafted":"0","date_planted":"0","date_eliminated":"0","date_labeled":"0","genuine_seedling":"0","offset":"0","row":"1","dont_eliminate":"0","note":"0","variety_id":"0","grafting":"0","rootstock":"0","experiment_site":"1"},"VarietiesView":{"id":"0","convar":"0","official_name":"0","acronym":"0","plant_breeder":"0","registration":"0","description":"0","batch_id":"0"},"MarkProperties":{"22":{"check":"0","mode":"count","operator":"","value":""},"13":{"check":"0","mode":"count","operator":"","value":""},"8":{"check":"0","mode":"count","operator":"","value":""},"19":{"check":"0","mode":"count","operator":"","value":""},"7":{"check":"0","mode":"all","operator":"","value":"0"},"14":{"check":"0","mode":"count","operator":"","value":""},"6":{"check":"0","mode":"count","operator":"","value":""},"21":{"check":"0","mode":"all","operator":"","value":""},"17":{"check":"0","mode":"count","operator":"","value":""},"20":{"check":"0","mode":"count","operator":"","value":""},"12":{"check":"0","mode":"all","operator":"","value":""},"10":{"check":"0","mode":"count","operator":"","value":""},"16":{"check":"0","mode":"all","operator":"","value":"0"},"5":{"check":"0","mode":"all","operator":"","value":"0"},"4":{"check":"0","mode":"count","operator":"","value":""},"3":{"check":"0","mode":"count","operator":"","value":""},"9":{"check":"0","mode":"count","operator":"","value":""},"15":{"check":"0","mode":"count","operator":"","value":""},"11":{"check":"0","mode":"count","operator":"","value":""},"18":{"check":"0","mode":"count","operator":"","value":""},"1":{"check":"0","mode":"count","operator":"","value":""},"2":{"check":"0","mode":"count","operator":"","value":""}}},"where":"{\\"condition\\":\\"AND\\",\\"rules\\":[{\\"id\\":\\"TreesView.experiment_site\\",\\"field\\":\\"TreesView.experiment_site\\",\\"type\\":\\"string\\",\\"input\\":\\"select\\",\\"operator\\":\\"equal\\",\\"value\\":\\"'.$experimentSite->name.'\\"}],\\"valid\\":true}"}',
            'description'    => $faker->sentence,
            'query_group_id' => $group->id,
        ];

        $query = $this->getEntityQueryFromArray( $data );

        $this->Table->deleteManyOrFail( $query->all() );

        return $data;
    }

    private function getEntityQueryFromArray( array $data ): \Cake\ORM\Query {
        return $this->Table->find()
                           ->where( [ self::TABLE . '.code' => $data['code'] ] );
    }
}
