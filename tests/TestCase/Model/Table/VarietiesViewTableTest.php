<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\VarietiesViewTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\VarietiesViewTable Test Case
 */
class VarietiesViewTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\VarietiesViewTable
     */
    public $VarietiesView;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.varieties_view',
        'app.batches',
        'app.crossings',
        'app.varieties',
        'app.scions_bundles',
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
        'app.mother_trees'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = TableRegistry::exists('VarietiesView') ? [] : ['className' => 'App\Model\Table\VarietiesViewTable'];
        $this->VarietiesView = TableRegistry::get('VarietiesView', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->VarietiesView);

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
