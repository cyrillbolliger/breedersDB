<?php

declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Model\Entity\Variety;
use App\Model\Table\VarietiesTable;
use App\Test\TestCase\Controller\Shared\VarietiesControllerTestTrait;
use App\Test\Util\AjaxTrait;
use App\Test\Util\AuthenticateTrait;
use App\Test\Util\DependsOnFixtureTrait;
use App\Test\Util\ExperimentSiteTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\VarietiesController Test Case
 *
 * @uses \App\Controller\VarietiesController
 */
class VarietiesControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use DependsOnFixtureTrait;
    use AuthenticateTrait;
    use ExperimentSiteTrait;
    use AjaxTrait;
    use VarietiesControllerTestTrait;

    private const ENDPOINT = '/varieties';
    private const TABLE = 'Varieties';
    private const CONTAINS = [
        'Batches'
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
        $this->addEntity();

        $this->get(self::ENDPOINT);

        $this->assertResponseSuccess();
        $this->assertResponseCode(200);

        $query = $this->Table
            ->find()
            ->orderDesc(self::TABLE . '.modified')
            ->limit(100)
            ->all();

        /** @var Variety $first */
        $first = $query->first();
        $last = $query->last();

        $this->assertResponseContains($first->convar);
        $this->assertResponseContains($last->convar);
    }

    /**
     * Test view method
     *
     * @return void
     */
    public function testView(): void
    {
        $variety = $this->addEntity();

        $this->get(self::ENDPOINT . "/view/{$variety->id}");

        $this->assertResponseSuccess();
        $this->assertResponseCode(200);

        $this->assertResponseContains($variety->convar);
        $this->assertResponseRegExp("/{$variety->batch->crossing_batch}(?!\.{$variety->code})/");

        // todo: related trees, related scions bundles, related marks
        self::markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test addBreederVariety method
     *
     * @return void
     */
    public function testAddBreederVariety(): void
    {
        $data = $this->getNonExistingEntityData();

        unset(
            $data['official_name'],
            $data['acronym'],
            $data['plant_breeder'],
            $data['registration']
        );

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post(self::ENDPOINT . '/add-breeder-variety', $data);

        $this->assertResponseSuccess();
        $this->assertEntityExists($data);

        $this->Table->deleteManyOrFail($this->getEntityQueryFromArray($data));
    }

    /**
     * Test addOfficialVariety method
     *
     * @return void
     */
    public function testAddOfficialVariety(): void
    {
        $data = $this->getNonExistingEntityData();
        $data['batch_id'] = 1;

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post(self::ENDPOINT . '/add-official-variety', $data);

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
        $variety = $this->addEntity();

        $data = [
            'code' => 'changed',
            'official_name' => 'changed',
            'acronym' => 'swap',
            'plant_breeder' => 'changed',
            'registration' => 'changed',
            'description' => 'changed',
            'batch_id' => $variety->batch_id,
        ];

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post(self::ENDPOINT . "/edit/{$variety->id}", $data);

        $this->assertResponseSuccess();
        $this->assertEntityExists($data);

        $this->Table->deleteManyOrFail($this->getEntityQueryFromArray($data));
    }

    /**
     * Test delete method
     *
     * @return void
     */
    public function testDelete(): void
    {
        $variety = $this->addEntity();

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->delete(self::ENDPOINT . "/delete/{$variety->id}");
        $this->assertResponseSuccess();

        $query = $this->getEntityQueryFromArray($variety->toArray());
        self::assertEquals(0, $query->count());
    }

    /**
     * Test searchCrossingBatchs method
     *
     * @return void
     */
    public function testSearchCrossingBatchs(): void
    {
        $variety = $this->addEntity();

        $this->setAjaxHeader();
        $this->get(self::ENDPOINT . '/searchCrossingBatchs?term=' . $variety->batch->crossing_batch);
        $this->assertResponseSuccess();
        $this->assertResponseContains($variety->batch->crossing_batch);
    }

    /**
     * Test searchConvars method
     *
     * @return void
     */
    public function testSearchConvars(): void
    {
        $variety = $this->addEntity();

        $this->setAjaxHeader();
        $this->get('/varieties/searchConvars?term=' . $variety->convar);
        $this->assertResponseSuccess();
        $this->assertResponseContains($variety->convar);
    }

    /**
     * Test getNextFreeCode method
     *
     * @return void
     */
    public function testGetNextFreeCode(): void
    {
        $variety = $this->addEntity();

        $greatestCodeVariety = $this->Table->find()
            ->where(['batch_id' => $variety->batch_id])
            ->order(['code' => 'DESC'])
            ->first();

        $expectedCode = (string)sprintf('%03d', (int)$greatestCodeVariety->code + 1);

        $this->setAjaxHeader();
        $this->get(self::ENDPOINT . '/getNextFreeCode?batch_id=' . $variety->batch_id);
        $this->assertResponseSuccess();
        $this->assertResponseContains($expectedCode);
    }

    /**
     * Test filter method
     *
     * @return void
     */
    public function testFilter(): void
    {
        $variety = $this->addEntity();

        $this->setAjaxHeader();
        $this->get(
            self::ENDPOINT . '/filter?fields%5B%5D=convar&fields%5B%5D=breeder_variety_code&fields%5B%5D=id&term=' . $variety->id
        );
        $this->assertResponseSuccess();
        $this->assertResponseContains($variety->convar);

        $this->setAjaxHeader();
        $this->get(
            self::ENDPOINT . '/filter?fields%5B%5D=convar&fields%5B%5D=breeder_variety_code&fields%5B%5D=id&term=' . $variety->convar
        );
        $this->assertResponseSuccess();
        $this->assertResponseContains($variety->convar);

        $this->setAjaxHeader();
        $this->get(
            self::ENDPOINT . '/filter?fields%5B%5D=convar&fields%5B%5D=breeder_variety_code&fields%5B%5D=id&term=' . COMPANY_ABBREV . $variety->id
        );
        $this->assertResponseSuccess();
        $this->assertResponseContains($variety->convar);
    }

    protected function setUp(): void
    {
        $this->authenticate();
        $this->setSite();
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->Table = $this->getTable('Varieties');
        parent::setUp();

        $this->setUnlockedFields(['code', 'batch_id']);
    }
}
