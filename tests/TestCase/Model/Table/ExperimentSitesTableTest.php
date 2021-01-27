<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ExperimentSitesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ExperimentSitesTable Test Case
 */
class ExperimentSitesTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\ExperimentSitesTable
     */
    public $ExperimentSites;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.experiment_sites',
        'app.trees',
        'app.varieties',
        'app.batches',
        'app.crossings',
        'app.marks',
        'app.scions_bundles',
        'app.rootstocks',
        'app.graftings',
        'app.rows'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = TableRegistry::exists('ExperimentSites') ? [] : ['className' => 'App\Model\Table\ExperimentSitesTable'];
        $this->ExperimentSites = TableRegistry::get('ExperimentSites', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->ExperimentSites);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
