<?php

declare(strict_types=1);

namespace App\Test\TestCase\Controller\REST1;

use App\Model\Table\QueriesTable;
use App\Test\TestCase\Controller\Shared\TreesControllerTestTrait;
use App\Test\Util\AjaxTrait;
use App\Test\Util\AuthenticateTrait;
use App\Test\Util\DependsOnFixtureTrait;
use App\Test\Util\ExperimentSiteTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\REST1\QueriesController Test Case
 *
 * @uses \App\Controller\REST1\QueriesController
 */
class QueriesControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use DependsOnFixtureTrait;
    use AuthenticateTrait;
    use ExperimentSiteTrait;
    use AjaxTrait;

    private const ENDPOINT = '/api/1/queries';
    private const TABLE = 'Queries';
    private const CONTAINS = [
    ];

    protected array $dependsOnFixture = [
        'ExperimentSites',
        'QueryGroups',
        'Queries',
    ];
    protected QueriesTable $Table;


    protected function setUp(): void
    {
        $this->authenticate();
        $this->setSite();
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->Table = $this->getTable(self::TABLE);
        parent::setUp();
    }

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndex(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test view method
     *
     * @return void
     * @uses \App\Controller\REST1\TreesController::view()
     */
    public function testView(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test view method
     *
     * @return void
     * @uses \App\Controller\REST1\TreesController::getTree()
     */
    public function testGetBaseFilterSchemas(): void
    {
        $this->setAjaxHeader();
        $this->get(self::ENDPOINT . '/get-base-filter-schemas');

        $this->assertResponseSuccess();
        $this->assertResponseCode(200);
        $this->assertContentType('application/json');

        $resp = json_decode((string)$this->_response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('Batches', $resp['data']);
        self::assertArrayHasKey('Crossings', $resp['data']);
        self::assertArrayHasKey('MotherTrees', $resp['data']);
        self::assertArrayHasKey('ScionsBundles', $resp['data']);
        self::assertArrayHasKey('Trees', $resp['data']);
        self::assertArrayHasKey('Varieties', $resp['data']);
    }

    /**
     * Test add method
     *
     * @return void
     * @uses \App\Controller\REST1\TreesController::add()
     */
    public function testAdd(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test edit method
     *
     * @return void
     * @uses \App\Controller\REST1\TreesController::edit()
     */
    public function testEdit(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test delete method
     *
     * @return void
     * @uses \App\Controller\REST1\TreesController::delete()
     */
    public function testDelete(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
