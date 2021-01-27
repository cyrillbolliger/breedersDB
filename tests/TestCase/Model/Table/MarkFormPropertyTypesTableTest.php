<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MarkFormPropertyTypesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MarkFormPropertyTypesTable Test Case
 */
class MarkFormPropertyTypesTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\MarkFormPropertyTypesTable
     */
    public $MarkFormPropertyTypes;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.mark_form_property_types',
        'app.mark_form_properties',
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
        'app.experiment_sites',
        'app.mark_values'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = TableRegistry::exists('MarkFormPropertyTypes') ? [] : ['className' => 'App\Model\Table\MarkFormPropertyTypesTable'];
        $this->MarkFormPropertyTypes = TableRegistry::get('MarkFormPropertyTypes', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->MarkFormPropertyTypes);

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
