<?php

declare(strict_types=1);

namespace App\Test\TestCase\Controller\REST1;

use App\Model\Table\TreesTable;
use App\Test\TestCase\Controller\Shared\TreesControllerTestTrait;
use App\Test\Util\AjaxTrait;
use App\Test\Util\AuthenticateTrait;
use App\Test\Util\DependsOnFixtureTrait;
use App\Test\Util\ExperimentSiteTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\REST1\TreesController Test Case
 *
 * @uses \App\Controller\REST1\TreesController
 */
class TreesControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use DependsOnFixtureTrait;
    use AuthenticateTrait;
    use ExperimentSiteTrait;
    use AjaxTrait;
    use TreesControllerTestTrait;

    private const ENDPOINT = '/api/1/trees';
    private const TABLE = 'Trees';
    private const CONTAINS = [
        'Varieties',
        'Rows',
        'ExperimentSites',
        'Rootstocks',
        'Graftings',
        'Marks',
    ];

    protected array $dependsOnFixture = self::CONTAINS;
    protected TreesTable $Table;

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
    public function testGetTree(): void
    {
        $entity = $this->addEntity();

        $this->setAjaxHeader();
        $this->get(self::ENDPOINT . "/getTree?fields%5B%5D=publicid&term={$entity->publicid}");

        $this->assertResponseSuccess();
        $this->assertResponseCode(200);
        $this->assertContentType('application/json');

        $tree = $this->Table
            ->find()
            ->contain(['Varieties', 'Rootstocks', 'Graftings', 'Rows', 'ExperimentSites'])
            ->where(['publicid' => $entity->publicid])
            ->firstOrFail();

        $tree->set(
            [
                'print' => [
                    'regular' => $this->Table->getLabelZpl($tree->id, 'convar'),
                    'anonymous' => $this->Table->getLabelZpl($tree->id, 'breeder_variety_code')
                ]
            ]
        );

        $expected = ['data' => $tree];
        $json = json_encode($expected, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        self::assertEquals($json, (string)$this->_response->getBody());

        $this->assertResponseRegExp("/\\\${\^XA.*\^FD{$entity->publicid}.*\^XZ}\\\$/");
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
