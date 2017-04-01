<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ScionsBundlesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ScionsBundlesTable Test Case
 */
class ScionsBundlesTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\ScionsBundlesTable
     */
    public $ScionsBundles;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.scions_bundles',
        'app.varieties',
        'app.batches',
        'app.crossings',
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
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('ScionsBundles') ? [] : ['className' => 'App\Model\Table\ScionsBundlesTable'];
        $this->ScionsBundles = TableRegistry::get('ScionsBundles', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->ScionsBundles);

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
