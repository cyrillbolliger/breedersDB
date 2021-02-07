<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CrossingsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\CrossingsTable Test Case
 */
class CrossingsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\CrossingsTable
     */
    public $Crossings;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.crossings',
        'app.varieties',
        'app.batches',
        'app.marks',
        'app.scions_bundles',
        'app.trees',
        'app.rootstocks',
        'app.graftings',
        'app.rows',
        'app.experiment_sites'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = TableRegistry::exists('Crossings') ? [] : ['className' => 'App\Model\Table\CrossingsTable'];
        $this->Crossings = TableRegistry::get('Crossings', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Crossings);

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
