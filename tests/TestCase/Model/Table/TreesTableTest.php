<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\TreesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\TreesTable Test Case
 */
class TreesTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\TreesTable
     */
    public $Trees;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.trees',
        'app.varieties',
        'app.batches',
        'app.crossings',
        'app.marks',
        'app.scions_bundles',
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
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Trees') ? [] : ['className' => 'App\Model\Table\TreesTable'];
        $this->Trees = TableRegistry::get('Trees', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Trees);

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
