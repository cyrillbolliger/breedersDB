<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MarkFormFieldsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MarkFormFieldsTable Test Case
 */
class MarkFormFieldsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\MarkFormFieldsTable
     */
    public $MarkFormFields;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
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
        'app.mark_values',
        'app.mark_form_properties'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('MarkFormFields') ? [] : ['className' => 'App\Model\Table\MarkFormFieldsTable'];
        $this->MarkFormFields = TableRegistry::get('MarkFormFields', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->MarkFormFields);

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
