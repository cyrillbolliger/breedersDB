<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MarksTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MarksTable Test Case
 */
class MarksTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\MarksTable
     */
    public $Marks;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.marks',
        'app.mark_forms',
        'app.trees',
        'app.varieties',
        'app.batches',
        'app.crossings',
        'app.scions_bundles',
        'app.rootstocks',
        'app.graftings',
        'app.rows',
        'app.experiment_sites',
        'app.mark_values'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Marks') ? [] : ['className' => 'App\Model\Table\MarksTable'];
        $this->Marks = TableRegistry::get('Marks', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Marks);

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
