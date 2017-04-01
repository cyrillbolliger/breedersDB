<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\RootstocksTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\RootstocksTable Test Case
 */
class RootstocksTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\RootstocksTable
     */
    public $Rootstocks;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.rootstocks',
        'app.trees',
        'app.varieties',
        'app.batches',
        'app.crossings',
        'app.marks',
        'app.scions_bundles',
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
        $config = TableRegistry::exists('Rootstocks') ? [] : ['className' => 'App\Model\Table\RootstocksTable'];
        $this->Rootstocks = TableRegistry::get('Rootstocks', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Rootstocks);

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
