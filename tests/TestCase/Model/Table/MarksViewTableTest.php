<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MarksViewTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MarksViewTable Test Case
 */
class MarksViewTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\MarksViewTable
     */
    public $MarksView;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.marks_view',
        'app.trees',
        'app.varieties',
        'app.batches',
        'app.crossings',
        'app.mother_trees',
        'app.marks',
        'app.mark_forms',
        'app.mark_form_fields',
        'app.mark_form_properties',
        'app.mark_form_property_types',
        'app.mark_values',
        'app.mark_scanner_codes',
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
        $config = TableRegistry::exists('MarksView') ? [] : ['className' => 'App\Model\Table\MarksViewTable'];
        $this->MarksView = TableRegistry::get('MarksView', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->MarksView);

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
