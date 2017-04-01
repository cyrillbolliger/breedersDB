<?php
namespace App\Test\TestCase\Controller;

use App\Controller\BatchesController;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\BatchesController Test Case
 */
class BatchesControllerTest extends IntegrationTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.batches',
        'app.crossings',
        'app.varieties',
        'app.scions_bundles',
        'app.trees',
        'app.rootstocks',
        'app.graftings',
        'app.rows',
        'app.experiment_sites',
        'app.marks'
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
