<?php

declare(strict_types=1);

namespace App\Test\TestCase\Controller\REST1;

use App\Controller\REST1\QueryGroupsController;
use App\Model\Table\QueryGroupsTable;
use App\Test\TestCase\Controller\Shared\QueryGroupsControllerTestTrait;
use App\Test\Util\AjaxTrait;
use App\Test\Util\AuthenticateTrait;
use App\Test\Util\DependsOnFixtureTrait;
use App\Test\Util\ExperimentSiteTrait;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\REST1\QueryGroupsController Test Case
 *
 * @uses \App\Controller\REST1\QueryGroupsController
 */
class QueryGroupsControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use DependsOnFixtureTrait;
    use AuthenticateTrait;
    use ExperimentSiteTrait;
    use AjaxTrait;
    use QueryGroupsControllerTestTrait;

    private const ENDPOINT = '/api/1/query-groups';
    private const TABLE = 'QueryGroups';
    private const CONTAINS = [
    ];

    protected array $dependsOnFixture = self::CONTAINS;
    protected QueryGroupsTable $Table;

    /**
     * Test index method
     *
     * @return void
     * @uses \App\Controller\REST1\QueryGroupsController::index()
     */
    public function testIndex(): void
    {
        $entity1 = $this->Table->newEntity($this->getNonExistingEntityData());
        $entity1->version = '1';
        $entity1->code = md5(uniqid('query-groups', true));

        $entity2 = $this->Table->newEntity($this->getNonExistingEntityData());
        $entity2->version = '1.0';
        $entity2->code = md5(uniqid('query-groups', true));

        $this->Table->saveManyOrFail([$entity1, $entity2]);

        $this->get(self::ENDPOINT);

        $this->assertResponseSuccess();
        $this->assertResponseCode(200);

        $this->assertResponseContains($entity1->code);
        $this->assertResponseContains($entity2->code);
    }

    /**
     * Test add method
     *
     * @return void
     * @uses \App\Controller\REST1\QueryGroupsController::add()
     */
    public function testAdd(): void
    {
        $data = [
            'code' => md5(uniqid('query-groups', true)),
        ];

        $this->enableCsrfToken();

        $this->post(self::ENDPOINT . '/add', ['data' => $data]);

        $this->assertResponseSuccess();

        $queryGroup = $this->Table->find()->all()->last();

        self::assertEquals('1.0', $queryGroup->version);

        $data = json_decode((string)$this->_response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $queryGroupArray = json_decode(json_encode($queryGroup->toArray()), true);
        unset($queryGroupArray['deleted']);

        self::assertEquals($queryGroupArray, $data['data']);
    }

    /**
     * Test view method
     *
     * @return void
     * @uses \App\Controller\REST1\QueryGroupsController::view()
     */
    public function testView(): void
    {
        $entity = $this->Table->newEntity($this->getNonExistingEntityData());
        $entity->version = '1';
        $entity->code = md5(uniqid('query-groups', true));
        $entity->deleted = null;

        $entity = $this->Table->saveOrFail($entity);

        $this->get(self::ENDPOINT . '/view/' . $entity->id);

        $this->assertResponseSuccess();

        $data = json_decode((string)$this->_response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $queryGroupArray = json_decode(json_encode($entity->toArray()), true);

        self::assertEquals($queryGroupArray, $data['data']);
    }

    /**
     * Test edit method
     *
     * @return void
     * @uses \App\Controller\REST1\QueryGroupsController::edit()
     */
    public function testEdit(): void
    {
        $entity = $this->Table->newEntity($this->getNonExistingEntityData());
        $entity->version = '1.0';
        $entity->code = md5(uniqid('query-groups', true));
        $entity->deleted = null;
        $entity = $this->Table->saveOrFail($entity);

        $newCode = md5(uniqid('query-groups', true));

        $this->enableCsrfToken();
        $this->patch(
            self::ENDPOINT . '/edit/' . $entity->id,
            ['data' => ['code' => $newCode]]
        );

        $this->assertResponseSuccess();

        $data = json_decode((string)$this->_response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $queryGroupArray = json_decode(json_encode($entity->toArray()), true);
        $queryGroupArray['code'] = $newCode;

        self::assertEquals($queryGroupArray, $data['data']);
    }

    /**
     * Test delete method
     *
     * @return void
     * @uses \App\Controller\REST1\QueryGroupsController::delete()
     */
    public function testDelete(): void
    {
        $entity = $this->Table->newEntity($this->getNonExistingEntityData());
        $entity->version = '1.0';
        $entity->code = md5(uniqid('query-groups', true));
        $entity->deleted = null;
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
