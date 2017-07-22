<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\BatchesViewTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\BatchesViewTable Test Case
 */
class BatchesViewTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\BatchesViewTable
     */
    public $BatchesView;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.batches_view',
        'app.crossings',
        'app.varieties',
        'app.batches',
        'app.marks',
        'app.mark_forms',
        'app.mark_form_fields',
        'app.mark_form_properties',
        'app.mark_form_property_types',
        'app.mark_values',
        'app.mark_scanner_codes',
        'app.trees',
        'app.rootstocks',
        'app.graftings',
        'app.rows',
        'app.experiment_sites',
        'app.scions_bundles',
        'app.mother_trees'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('BatchesView') ? [] : ['className' => 'App\Model\Table\BatchesViewTable'];
        $this->BatchesView = TableRegistry::get('BatchesView', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->BatchesView);

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
