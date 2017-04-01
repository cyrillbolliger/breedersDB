<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MarkFormPropertiesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MarkFormPropertiesTable Test Case
 */
class MarkFormPropertiesTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\MarkFormPropertiesTable
     */
    public $MarkFormProperties;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
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
        'app.experiment_sites',
        'app.mark_values'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('MarkFormProperties') ? [] : ['className' => 'App\Model\Table\MarkFormPropertiesTable'];
        $this->MarkFormProperties = TableRegistry::get('MarkFormProperties', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->MarkFormProperties);

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
