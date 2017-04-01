<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\VarietiesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\VarietiesTable Test Case
 */
class VarietiesTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\VarietiesTable
     */
    public $Varieties;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.varieties',
        'app.batches',
        'app.crossings',
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
        $config = TableRegistry::exists('Varieties') ? [] : ['className' => 'App\Model\Table\VarietiesTable'];
        $this->Varieties = TableRegistry::get('Varieties', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Varieties);

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
