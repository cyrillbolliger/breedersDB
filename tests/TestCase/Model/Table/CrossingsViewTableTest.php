<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CrossingsViewTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\CrossingsViewTable Test Case
 */
class CrossingsViewTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\CrossingsViewTable
     */
    public $CrossingsView;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.crossings_view'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = TableRegistry::exists('CrossingsView') ? [] : ['className' => 'App\Model\Table\CrossingsViewTable'];
        $this->CrossingsView = TableRegistry::get('CrossingsView', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->CrossingsView);

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
}
