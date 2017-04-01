<?php
namespace App\Test\TestCase\Controller;

use App\Controller\VarietiesController;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\VarietiesController Test Case
 */
class VarietiesControllerTest extends IntegrationTestCase
{

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
     * Test index method
     *
     * @return void
     */
    public function testIndex()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test view method
     *
     * @return void
     */
    public function testView()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test add method
     *
     * @return void
     */
    public function testAdd()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test edit method
     *
     * @return void
     */
    public function testEdit()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test delete method
     *
     * @return void
     */
    public function testDelete()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
