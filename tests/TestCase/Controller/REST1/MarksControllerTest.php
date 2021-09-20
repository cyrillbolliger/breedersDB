<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\REST1;

use App\Controller\REST1\MarksController;
use App\Model\Entity\Mark;
use App\Model\Entity\MarkForm;
use App\Model\Entity\Tree;
use App\Model\Table\MarksTable;
use App\Test\TestCase\Controller\Shared\MarksControllerTestTrait;
use App\Test\Util\AjaxTrait;
use App\Test\Util\AuthenticateTrait;
use App\Test\Util\DependsOnFixtureTrait;
use App\Test\Util\ExperimentSiteTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\REST1\MarksController Test Case
 *
 * @uses \App\Controller\REST1\MarksController
 */
class MarksControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use DependsOnFixtureTrait;
    use AuthenticateTrait;
    use ExperimentSiteTrait;
    use AjaxTrait;
    use MarksControllerTestTrait;

    private const ENDPOINT = '/api/1/marks';
    private const TABLE = 'Marks';
    private const CONTAINS = [
        'Batches',
        'Varieties',
        'Trees',
        'MarkValues',
        'MarkValues.MarkFormProperties',
    ];

    protected array $dependsOnFixture = [
        'Batches',
        'Varieties',
        'Trees',
        'MarkFormPropertyTypes',
        'MarkFormProperties',
        'Marks',
        'MarkValues',
    ];
    protected MarksTable $Table;

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
     * @uses \App\Controller\REST1\MarksController::index()
     */
    public function testIndex(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test view method
     *
     * @return void
     * @uses \App\Controller\REST1\MarksController::view()
     */
    public function testView(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test add method
     *
     * @return void
     * @uses \App\Controller\REST1\MarksController::add()
     */
    public function testAdd(): void
    {
        /** @var MarkForm $form */
        $form = $this->getTable('MarkForms')
                     ->find()
                     ->contain(['MarkFormFields', 'MarkFormFields.MarkFormProperties'])
                     ->matching('MarkFormFields', function ($q) {
                         return $q->where(['MarkFormFields.mark_form_id = MarkForms.id']);
                     })
                     ->firstOrFail();

        /** @var Tree $tree */
        $tree = $this->getTable('Trees')
                     ->find()
                     ->firstOrFail();

        $faker = \Faker\Factory::create();

        $mark_values = [];
        foreach ($form->mark_form_fields as $field) {
            $property = $field->mark_form_property;

            switch ($property->field_type) {
                case 'FLOAT':
                case 'INTEGER':
                    $value = $faker->numberBetween($property->number_constraints->min,
                        $property->number_constraints->max);
                    break;
                case 'BOOLEAN':
                    $value = $faker->boolean;
                    break;
                case 'DATE':
                    $value = $faker->date();
                    break;
                default:
                    $value = $faker->sentence;
            }

            $mark_values[] = [
                'value'                 => $value,
                'exceptional_mark'      => false,
                'mark_form_property_id' => $property->id
            ];
        }

        $data = [
            'date'         => '2021-09-19T00:00:00.000Z',
            'author'       => 'Hugo',
            'mark_form_id' => $form->id,
            'tree_id'      => $tree->id,
            'variety_id'   => null,
            'batch_id'     => null,
            'mark_values'  => $mark_values
        ];

        $this->enableCsrfToken();

        $this->post(self::ENDPOINT.'/add', ['data' => $data]);

        $this->assertResponseSuccess();

        /** @var Mark $mark */
        $mark = $this->getTable('Marks')
                     ->find()
                     ->contain(['MarkValues'])
                     ->last();

        $expectedResponse = [
            'id' => $mark->id
        ];

        $this->assertJson(json_encode(['data' => $expectedResponse], JSON_THROW_ON_ERROR));

        self::assertEquals('2021-09-19', $mark->date->toDateString());
        self::assertEquals($data['author'], $mark->author);
        self::assertEquals($data['mark_form_id'], $mark->mark_form_id);
        self::assertCount(count($data['mark_values']), $mark->mark_values);

        foreach ($data['mark_values'] as $idx => $expected) {
            self::assertEquals($expected['value'], $mark->mark_values[$idx]->value);
            self::assertEquals($expected['exceptional_mark'], $mark->mark_values[$idx]->exceptional_mark);
            self::assertEquals($expected['mark_form_property_id'], $mark->mark_values[$idx]->mark_form_property_id);
            self::assertEquals($mark->id, $mark->mark_values[$idx]->mark_id);
        }
    }

    /**
     * Test edit method
     *
     * @return void
     * @uses \App\Controller\REST1\MarksController::edit()
     */
    public function testEdit(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test delete method
     *
     * @return void
     * @uses \App\Controller\REST1\MarksController::delete()
     */
    public function testDelete(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
