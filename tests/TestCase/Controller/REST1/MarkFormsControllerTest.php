<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\REST1;

use App\Controller\REST1\MarkFormsController;
use App\Model\Entity\MarkForm;
use App\Model\Table\MarkFormsTable;
use App\Test\TestCase\Controller\Shared\MarkFormsControllerTestTrait;
use App\Test\Util\AjaxTrait;
use App\Test\Util\AuthenticateTrait;
use App\Test\Util\DependsOnFixtureTrait;
use App\Test\Util\ExperimentSiteTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\REST1\MarkFormsController Test Case
 *
 * @uses \App\Controller\REST1\MarkFormsController
 */
class MarkFormsControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use DependsOnFixtureTrait;
    use AuthenticateTrait;
    use ExperimentSiteTrait;
    use AjaxTrait;
    use MarkFormsControllerTestTrait;

    private const ENDPOINT = '/api/1/mark-forms';
    private const TABLE = 'MarkForms';
    private const CONTAINS = [
        'MarkFormFields',
    ];

    protected array $dependsOnFixture = self::CONTAINS;
    protected MarkFormsTable $Table;

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

    /**
     * Test view method
     *
     * @return void
     * @uses \App\Controller\REST1\MarkFormsController::view()
     */
    public function testView(): void
    {
        $entity = $this->addEntity();

        $this->get( self::ENDPOINT . "/view/{$entity->id}"  );

        $this->assertResponseSuccess();
        $this->assertResponseCode( 200 );
        $this->assertContentType('application/json');

        $query = $this->Table
            ->get($entity->id, [
                'contain' => [
                    'MarkFormProperties' => [
                        'sort' => ['MarkFormFields.priority' => 'ASC'],
                    ],
                ],
            ]);

        $data = $query->toArray();
        foreach($data['mark_form_properties'] as $idx => $markFormProperty){
            unset( $data['mark_form_properties'][$idx]['_joinData']);
        }

        $expected = ['data' => $data];
        $json = json_encode($expected, JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR);
        self::assertEquals($json, (string)$this->_response->getBody());
    }

    /**
     * Test add method
     *
     * @return void
     * @uses \App\Controller\REST1\MarkFormsController::add()
     */
    public function testAdd(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test edit method
     *
     * @return void
     * @uses \App\Controller\REST1\MarkFormsController::edit()
     */
    public function testEdit(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test delete method
     *
     * @return void
     * @uses \App\Controller\REST1\MarkFormsController::delete()
     */
    public function testDelete(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
