<?php

declare(strict_types=1);

namespace App\Test\TestCase\Controller\REST1;

use App\Model\Entity\QueryGroup;
use App\Model\Table\QueriesTable;
use App\Test\TestCase\Controller\Shared\QueriesControllerTestTrait;
use App\Test\Util\AjaxTrait;
use App\Test\Util\AuthenticateTrait;
use App\Test\Util\DependsOnFixtureTrait;
use App\Test\Util\ExperimentSiteTrait;
use Cake\Datasource\Exception\RecordNotFoundException;
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
    use QueriesControllerTestTrait;

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

    private QueryGroup $queryGroupVersion1;

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndex(): void
    {
        $entity1 = $this->Table->newEntity($this->getNonExistingEntityData());
        $entity1->code = md5(uniqid('query', true));
        $entity1->query_group = $this->getQueryGroup();
        $entity1->my_query = json_encode(['fake' => 'query']);

        $entity2 = $this->Table->newEntity($this->getNonExistingEntityData());
        $entity2->code = md5(uniqid('query', true));
        $entity2->query_group = $this->getQueryGroup();
        $entity2->my_query = json_encode(['fake' => 'query']);

        $this->Table->saveManyOrFail([$entity1, $entity2]);

        $this->get(self::ENDPOINT);

        $this->assertResponseSuccess();
        $this->assertResponseCode(200);

        $this->assertResponseContains($entity1->code);
        $this->assertResponseContains($entity2->code);
    }

    private function getQueryGroup(): QueryGroup
    {
        if (!isset($this->queryGroupVersion1)) {
            $queryGroupsTable = $this->getTable('QueryGroups');

            $queryGroup = $queryGroupsTable->newEntity(
                [
                    'code' => md5(uniqid('queries', true)),
                    'version' => '1.0'
                ]
            );

            /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
            $this->queryGroupVersion1 = $queryGroupsTable
                ->saveOrFail($queryGroup);
        }

        return $this->queryGroupVersion1;
    }

    /**
     * Test view method
     *
     * @return void
     * @uses \App\Controller\REST1\TreesController::view()
     */
    public function testView(): void
    {
        $entity = $this->Table->newEntity($this->getNonExistingEntityData());
        $entity->code = md5(uniqid('query', true));
        $entity->query_group_id = $this->getQueryGroup()->id;
        $entity->my_query = json_encode(['fake' => 'query']);
        $entity->deleted = null;

        $entity = $this->Table->saveOrFail($entity);

        $this->get(self::ENDPOINT . '/view/' . $entity->id);

        $this->assertResponseSuccess();

        $data = json_decode((string)$this->_response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $queryArray = json_decode(json_encode($entity->toArray()), true);
        $queryArray['raw_query'] = ['fake' => 'query'];

        self::assertEquals($queryArray, $data['data']);
    }

    /**
     * Test view method
     *
     * @return void
     * @uses \App\Controller\REST1\TreesController::getTree()
     */
    public function testGetFilterSchemas(): void
    {
        $this->setAjaxHeader();
        $this->get(self::ENDPOINT . '/get-filter-schemas');

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
        self::assertArrayHasKey('Marks', $resp['data']);
    }

    /**
     * Test add method
     *
     * @return void
     * @uses \App\Controller\REST1\TreesController::add()
     */
    public function testAdd(): void
    {
        $data = [
            'code' => md5(uniqid('query', true)),
            'description' => 'very important query',
            'raw_query' => ['some' => 'query data'],
            'query_group_id' => $this->getQueryGroup()->id,
        ];

        $this->enableCsrfToken();

        $this->post(self::ENDPOINT . '/add', ['data' => $data]);

        $this->assertResponseSuccess();

        $query = $this->Table->find()->all()->last();

        $data = json_decode((string)$this->_response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $queryArray = json_decode(json_encode($query->toArray()), true);
        $queryArray['raw_query'] = ['some' => 'query data'];

        self::assertEquals($queryArray, $data['data']);
    }

    /**
     * Test edit method
     *
     * @return void
     * @uses \App\Controller\REST1\TreesController::edit()
     */
    public function testEdit(): void
    {
        $entity = $this->Table->newEntity($this->getNonExistingEntityData());
        $entity->code = md5(uniqid('query', true));
        $entity->my_query = json_encode(['fake' => 'query']);
        $entity->query_group_id = $this->getQueryGroup()->id;
        $entity->deleted = null;
        $entity = $this->Table->saveOrFail($entity);

        $newCode = md5(uniqid('query', true));

        $this->enableCsrfToken();
        $this->patch(
            self::ENDPOINT . '/edit/' . $entity->id,
            ['data' => ['code' => $newCode, 'raw_query' => ['query' => 'has changed']]]
        );

        $this->assertResponseSuccess();

        $data = json_decode((string)$this->_response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $queryArray = json_decode(json_encode($entity->toArray()), true);
        $queryArray['code'] = $newCode;
        $queryArray['raw_query'] = ['query' => 'has changed'];
        $queryArray['my_query'] = json_encode(['query' => 'has changed']);

        self::assertEquals($queryArray, $data['data']);
    }

    /**
     * Test delete method
     *
     * @return void
     * @uses \App\Controller\REST1\TreesController::delete()
     */
    public function testDelete(): void
    {
        $entity = $this->Table->newEntity($this->getNonExistingEntityData());
        $entity->code = md5(uniqid('query', true));
        $entity = $this->Table->saveOrFail($entity);

        $this->enableCsrfToken();
        $this->delete(self::ENDPOINT . '/delete/' . $entity->id);

        $this->assertResponseSuccess();
        $this->assertResponseCode(204);

        $this->expectException(RecordNotFoundException::class);
        $this->Table->get($entity->id);
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
