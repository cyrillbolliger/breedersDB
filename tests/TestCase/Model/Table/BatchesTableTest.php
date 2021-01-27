<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\BatchesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\BatchesTable Test Case
 */
class BatchesTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\BatchesTable
     */
    public $Batches;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.batches',
        'app.crossings',
        'app.varieties',
        'app.scions_bundles',
        'app.trees',
        'app.rootstocks',
        'app.graftings',
        'app.rows',
        'app.experiment_sites',
        'app.marks'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = TableRegistry::exists('Batches') ? [] : ['className' => 'App\Model\Table\BatchesTable'];
        $this->Batches = TableRegistry::get('Batches', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Batches);

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
