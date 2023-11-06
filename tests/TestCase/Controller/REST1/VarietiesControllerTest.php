<?php

declare(strict_types=1);

namespace App\Test\TestCase\Controller\REST1;

use App\Model\Table\VarietiesTable;
use App\Test\TestCase\Controller\Shared\VarietyControllerTestTrait;
use App\Test\Util\AjaxTrait;
use App\Test\Util\AuthenticateTrait;
use App\Test\Util\DependsOnFixtureTrait;
use App\Test\Util\ExperimentSiteTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\REST1\VarietiesController Test Case
 *
 * @uses \App\Controller\REST1\VarietiesController
 */
class VarietiesControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use DependsOnFixtureTrait;
    use AuthenticateTrait;
    use ExperimentSiteTrait;
    use AjaxTrait;
    use VarietyControllerTestTrait;

    private const ENDPOINT = '/api/1/varieties';
    private const TABLE = 'Varieties';
    private const CONTAINS = [
        'Batches',
    ];

    protected array $dependsOnFixture = self::CONTAINS;
    protected VarietiesTable $Table;

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndex(): void
    {
        $entity1 = $this->addEntity('999');
        $entity2 = $this->addEntity('998');
        $entity3 = $this->addEntity('997');

        $this->setAjaxHeader();
        $this->get(self::ENDPOINT);

        $this->assertResponseSuccess();
        $this->assertResponseCode(200);
        $this->assertContentType('application/json');

        $expected = [
            'data' => [
                'count' => 3,
                'offset' => 0,
                'sortBy' => null,
                'order' => null,
                'limit' => null,
                'results' => [$entity1, $entity2, $entity3]
            ]
        ];
        $json = json_encode($expected, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        self::assertEquals($json, (string)$this->_response->getBody());
    }

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndexLimitOffsetSorted(): void
    {
        $entity1 = $this->addEntity('999');
        $entity2 = $this->addEntity('998');
        $entity3 = $this->addEntity('997');

        $this->setAjaxHeader();
        $this->get(self::ENDPOINT . '?limit=1&offset=2&sortBy=convar&order=asc');

        $this->assertResponseSuccess();
        $this->assertResponseCode(200);
        $this->assertContentType('application/json');

        $expected = [
            'data' => [
                'count' => 3,
                'offset' => 2,
                'sortBy' => 'convar',
                'order' => 'asc',
                'limit' => 1,
                'results' => [$entity1]
            ]
        ];
        $json = json_encode($expected, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        self::assertEquals($json, (string)$this->_response->getBody());
    }

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndexFiltered(): void
    {
        $entity1 = $this->addEntity('999');
        $entity2 = $this->addEntity('998');
        $entity3 = $this->addEntity('997');

        $this->setAjaxHeader();
        $this->get(self::ENDPOINT . '?term=..998');

        $this->assertResponseSuccess();
        $this->assertResponseCode(200);
        $this->assertContentType('application/json');

        $expected = [
            'data' => [
                'count' => 1,
                'offset' => 0,
                'sortBy' => null,
                'order' => null,
                'limit' => null,
                'results' => [$entity2]
            ]
        ];
        $json = json_encode($expected, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        self::assertEquals($json, (string)$this->_response->getBody());
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

    protected function setUp(): void
    {
        $this->authenticate();
        $this->setSite();
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->Table = $this->getTable(self::TABLE);
        parent::setUp();
    }
}
