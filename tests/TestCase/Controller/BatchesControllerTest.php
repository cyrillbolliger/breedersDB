<?php

declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Model\Entity\Batch;
use App\Model\Table\BatchesTable;
use App\Test\TestCase\Controller\Shared\BatchesControllerTestTrait;
use App\Test\Util\AjaxTrait;
use App\Test\Util\AuthenticateTrait;
use App\Test\Util\DependsOnFixtureTrait;
use App\Test\Util\ExperimentSiteTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\BatchesController Test Case
 *
 * @uses \App\Controller\BatchesController
 */
class BatchesControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use DependsOnFixtureTrait;
    use AuthenticateTrait;
    use ExperimentSiteTrait;
    use AjaxTrait;
    use BatchesControllerTestTrait;

    private const ENDPOINT = '/batches';
    private const TABLE = 'Batches';
    private const CONTAINS = [
        'Crossings'
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
        $this->addEntity();

        $this->get(self::ENDPOINT);

        $this->assertResponseSuccess();
        $this->assertResponseCode(200);

        $query = $this->Table
            ->find()
            ->where(['Batches.id !=' => 1])
            ->orderDesc('Batches.modified')
            ->limit(100)
            ->all();

        /** @var Batch $first */
        $first = $query->first();
        $last = $query->last();

        $this->assertResponseContains($first->crossing_batch);
        $this->assertResponseContains($last->crossing_batch);
    }

    /**
     * Test view method
     *
     * @return void
     */
    public function testView(): void
    {
        $batch = $this->addEntity();

        $this->get(self::ENDPOINT. "/view/{$batch->id}");

        $this->assertResponseSuccess();
        $this->assertResponseCode(200);

        $this->assertResponseContains($batch->crossing_batch);

        // todo: test varieties
        // todo: test marks
        self::markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test add method
     *
     * @return void
     */
    public function testAdd(): void
    {
        $data = $this->getNonExistingEntityData();

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post(self::ENDPOINT. '/add', $data);

        $this->assertResponseSuccess();
        $this->assertEntityExists($data);

        $this->Table->deleteManyOrFail($this->getEntityQueryFromArray($data));
    }

    /**
     * Test edit method
     *
     * @return void
     */
    public function testEdit(): void
    {
        $batch = $this->addEntity();

        $changed = [
            'crossing_id' => $batch->crossing_id,
            'code' => '99Z',
            'date_sowed' => '05.03.2021',
            'numb_seeds_sowed' => 3,
            'numb_sprouts_grown' => 6,
            'seed_tray' => '21',
            'date_planted' => '02.07.2021',
            'numb_sprouts_planted' => 5,
            'patch' => 'The newest patch',
            'note' => 'This is not very important',
        ];

        $testEntities = $this->getEntityQueryFromArray($changed)
            ->find('all', ['withDeleted']);
        foreach ($testEntities as $testEntity) {
            $this->Table->hardDelete($testEntity);
        }

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post(self::ENDPOINT. "/edit/{$batch->id}", $changed);

        $this->assertResponseSuccess();
        $this->assertEntityExists($changed);

        $this->Table->deleteManyOrFail($this->getEntityQueryFromArray($changed));
    }

    /**
     * Test delete method
     *
     * @return void
     */
    public function testDelete(): void
    {
        $batch = $this->addEntity();

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->delete(self::ENDPOINT. "/delete/{$batch->id}");
        $this->assertResponseSuccess();

        $query = $this->getEntityQueryFromArray($batch->toArray());
        self::assertEquals(0, $query->count());
    }

    /**
     * Test filter method
     *
     * @return void
     */
    public function testFilter_crossingBatch(): void
    {
        $batch = $this->addEntity();

        $this->setAjaxHeader();
        $this->get(self::ENDPOINT. '/filter?fields%5B%5D=crossing_batch&term=' . $batch->crossing_batch);
        $this->assertResponseSuccess();
        $this->assertResponseContains($batch->crossing_batch);
    }

    /**
     * Test filter method
     *
     * @return void
     */
    public function testFilter_crossing(): void
    {
        $batch = $this->addEntity();

        $this->setAjaxHeader();
        $this->get(self::ENDPOINT. '/filter?fields%5B%5D=crossing_batch&term=' . explode('.', $batch->crossing_batch)[0]);
        $this->assertResponseSuccess();
        $this->assertResponseContains($batch->crossing_batch);
    }

    /**
     * Test filter method
     *
     * @return void
     */
    public function testFilter_batch(): void
    {
        $batch = $this->addEntity();

        $this->setAjaxHeader();
        $this->get(self::ENDPOINT. '/filter?fields%5B%5D=crossing_batch&term=.' . explode('.', $batch->crossing_batch)[1]);
        $this->assertResponseSuccess();
        $this->assertResponseContains($batch->crossing_batch);
    }

    /**
     * Test filter method
     *
     * @return void
     */
    public function testFilter_nothing(): void
    {
        $this->setAjaxHeader();
        $this->get(self::ENDPOINT. '/filter?fields%5B%5D=crossing_batch&term=thisStringDoesNeverMatch');
        $this->assertResponseSuccess();
        $this->assertResponseContains('nothing_found');
    }

    /**
     * Test print method
     *
     * @return void
     */
    public function testPrint(): void
    {
        $batch = $this->addEntity();
        $this->get(self::ENDPOINT. "/print/{$batch->id}/view/{$batch->id}");
        $this->assertResponseSuccess();
        $this->assertResponseContains('print_button_regular');
        $this->assertResponseContains($batch->crossing_batch);
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
