<?php

declare(strict_types=1);

namespace App\Test\TestCase\Controller\REST1;

use App\Model\Table\BatchesTable;
use App\Model\Table\VarietiesTable;
use App\Test\TestCase\Controller\Shared\BatchesControllerTestTrait;
use App\Test\TestCase\Controller\Shared\VarietiesControllerTestTrait;
use App\Test\Util\AjaxTrait;
use App\Test\Util\AuthenticateTrait;
use App\Test\Util\DependsOnFixtureTrait;
use App\Test\Util\ExperimentSiteTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\REST1\BatchesController Test Case
 *
 * @uses \App\Controller\REST1\BatchesController
 */
class BatchesControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use DependsOnFixtureTrait;
    use AuthenticateTrait;
    use ExperimentSiteTrait;
    use AjaxTrait;
    use BatchesControllerTestTrait;

    private const ENDPOINT = '/api/1/batches';
    private const TABLE = 'Batches';
    private const CONTAINS = [
        'Crossings',
    ];

    protected array $dependsOnFixture = [ self::TABLE, ...self::CONTAINS ];
    protected BatchesTable $Table;

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndex(): void
    {
        $entity1 = $this->addEntity('99Z');
        $entity2 = $this->addEntity('99Y');
        $entity3 = $this->addEntity('99X');

        $this->setAjaxHeader();
        $this->get(self::ENDPOINT);

        $this->assertResponseSuccess();
        $this->assertResponseCode(200);
        $this->assertContentType('application/json');

        $expected = [
            'data' => [
                'count' => $this->Table->find()->count(),
                'offset' => 0,
                'sortBy' => null,
                'order' => null,
                'limit' => null,
                'results' => $this->Table->find()->contain(self::CONTAINS)
            ]
        ];
        $json = json_encode($expected, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR | JSON_HEX_QUOT | JSON_HEX_APOS);
        self::assertEquals($json, (string)$this->_response->getBody());
    }

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndexLimitOffsetSorted(): void
    {
        $entity1 = $this->addEntity('99Z');
        $entity2 = $this->addEntity('99Y');
        $entity3 = $this->addEntity('99X');

        $this->setAjaxHeader();
        $this->get(self::ENDPOINT . '?limit=1&offset=2&sortBy=code&order=desc');

        $this->assertResponseSuccess();
        $this->assertResponseCode(200);
        $this->assertContentType('application/json');

        $expected = [
            'data' => [
                'count' => $this->Table->find()->count(),
                'offset' => 2,
                'sortBy' => 'Batches.code',
                'order' => 'desc',
                'limit' => 1,
                'results' => [$entity3]
            ]
        ];
        $json = json_encode($expected, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR | JSON_HEX_QUOT | JSON_HEX_APOS);
        self::assertEquals($json, (string)$this->_response->getBody());
    }

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndexFiltered(): void
    {
        $entity1 = $this->addEntity('99Z');
        $entity2 = $this->addEntity('99Y');
        $entity3 = $this->addEntity('99X');

        $this->setAjaxHeader();
        $this->get(self::ENDPOINT . '?term=.99Y');

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
        $json = json_encode($expected, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR | JSON_HEX_QUOT | JSON_HEX_APOS);
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
