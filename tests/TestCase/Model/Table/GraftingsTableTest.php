<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\GraftingsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\GraftingsTable Test Case
 */
class GraftingsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\GraftingsTable
     */
    public $Graftings;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.graftings',
        'app.trees',
        'app.varieties',
        'app.batches',
        'app.crossings',
        'app.marks',
        'app.scions_bundles',
        'app.rootstocks',
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
        $config = TableRegistry::exists('Graftings') ? [] : ['className' => 'App\Model\Table\GraftingsTable'];
        $this->Graftings = TableRegistry::get('Graftings', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Graftings);

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
