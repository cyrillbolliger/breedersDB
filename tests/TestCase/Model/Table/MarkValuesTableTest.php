<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MarkValuesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MarkValuesTable Test Case
 */
class MarkValuesTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\MarkValuesTable
     */
    public $MarkValues;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.mark_values',
        'app.mark_form_properties',
        'app.mark_form_property_types',
        'app.mark_form_fields',
        'app.mark_forms',
        'app.marks',
        'app.trees',
        'app.varieties',
        'app.batches',
        'app.crossings',
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
        $config = TableRegistry::exists('MarkValues') ? [] : ['className' => 'App\Model\Table\MarkValuesTable'];
        $this->MarkValues = TableRegistry::get('MarkValues', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->MarkValues);

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
