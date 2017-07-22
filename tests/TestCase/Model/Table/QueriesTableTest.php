<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\QueriesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\QueriesTable Test Case
 */
class QueriesTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\QueriesTable
     */
    public $Queries;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.queries',
        'app.query_groups'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Queries') ? [] : ['className' => 'App\Model\Table\QueriesTable'];
        $this->Queries = TableRegistry::get('Queries', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Queries);

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
