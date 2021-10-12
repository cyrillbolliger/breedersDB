<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\REST1;

use App\Model\Table\MarkFormPropertiesTable;
use App\Test\TestCase\Controller\Shared\MarkFormPropertiesControllerTestTrait;
use App\Test\Util\AjaxTrait;
use App\Test\Util\AuthenticateTrait;
use App\Test\Util\DependsOnFixtureTrait;
use App\Test\Util\ExperimentSiteTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\REST1\MarkFormsController Test Case
 *
 * @uses \App\Controller\REST1\MarkFormPropertiesController
 */
class MarkFormPropertiesControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use DependsOnFixtureTrait;
    use AuthenticateTrait;
    use ExperimentSiteTrait;
    use AjaxTrait;
    use MarkFormPropertiesControllerTestTrait;

    private const ENDPOINT = '/api/1/mark-form-properties';
    private const TABLE = 'MarkFormProperties';
    private const CONTAINS = [
        'MarkFormPropertyTypes',
        'MarkFormFields',
        'MarkValues',
    ];

    protected array $dependsOnFixture = [ 'MarkFormProperties' ] + self::CONTAINS;
    protected MarkFormPropertiesTable $Table;

    protected function setUp(): void {
        $this->authenticate();
        $this->setSite();
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->Table = $this->getTable( self::TABLE );
        parent::setUp();
    }

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndex(): void {
        $this->addEntity();

        $this->get( self::ENDPOINT );

        $this->assertResponseSuccess();
        $this->assertResponseCode( 200 );
        $this->assertContentType('application/json');

        $query = $this->Table
            ->find()
            ->orderAsc( self::TABLE . '.name' )
            ->all();

        $expected = ['data' => $query->toArray()];
        $json = json_encode($expected, JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR);
        self::assertEquals($json, (string)$this->_response->getBody());
    }
}
