<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\TreesViewTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\TreesViewTable Test Case
 */
class TreesViewTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\TreesViewTable
     */
    public $TreesView;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.trees_view',
        'app.varieties',
        'app.batches',
        'app.crossings',
        'app.mother_trees',
        'app.trees',
        'app.rootstocks',
        'app.graftings',
        'app.rows',
        'app.experiment_sites',
        'app.marks',
        'app.mark_forms',
        'app.mark_form_fields',
        'app.mark_form_properties',
        'app.mark_form_property_types',
        'app.mark_values',
        'app.mark_scanner_codes',
        'app.scions_bundles'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('TreesView') ? [] : ['className' => 'App\Model\Table\TreesViewTable'];
        $this->TreesView = TableRegistry::get('TreesView', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->TreesView);

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
