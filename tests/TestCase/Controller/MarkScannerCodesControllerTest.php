<?php
namespace App\Test\TestCase\Controller;

use App\Controller\MarkScannerCodesController;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\MarkScannerCodesController Test Case
 */
class MarkScannerCodesControllerTest extends IntegrationTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.mark_scanner_codes',
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
