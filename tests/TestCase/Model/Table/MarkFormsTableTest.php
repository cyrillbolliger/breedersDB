<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MarkFormsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MarkFormsTable Test Case
 */
class MarkFormsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\MarkFormsTable
     */
    public $MarkForms;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.mark_forms',
        'app.mark_form_fields',
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
        $config = TableRegistry::exists('MarkForms') ? [] : ['className' => 'App\Model\Table\MarkFormsTable'];
        $this->MarkForms = TableRegistry::get('MarkForms', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->MarkForms);

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
