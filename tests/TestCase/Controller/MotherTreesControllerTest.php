<?php
namespace App\Test\TestCase\Controller;

use App\Controller\MotherTreesController;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\MotherTreesController Test Case
 */
class MotherTreesControllerTest extends IntegrationTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.mother_trees',
        'app.crossings',
        'app.varieties',
        'app.batches',
        'app.marks',
        'app.mark_forms',
        'app.mark_form_fields',
        'app.mark_form_properties',
        'app.mark_form_property_types',
        'app.mark_values',
        'app.mark_scanner_codes',
        'app.trees',
        'app.rootstocks',
        'app.graftings',
        'app.rows',
        'app.experiment_sites',
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
