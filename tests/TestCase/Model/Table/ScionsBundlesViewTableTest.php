<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ScionsBundlesViewTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ScionsBundlesViewTable Test Case
 */
class ScionsBundlesViewTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\ScionsBundlesViewTable
     */
    public $ScionsBundlesView;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.scions_bundles_view',
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
    public function setUp(): void
    {
        parent::setUp();
        $config = TableRegistry::exists('ScionsBundlesView') ? [] : ['className' => 'App\Model\Table\ScionsBundlesViewTable'];
        $this->ScionsBundlesView = TableRegistry::get('ScionsBundlesView', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->ScionsBundlesView);

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
