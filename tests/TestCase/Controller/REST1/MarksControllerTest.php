<?php

declare(strict_types=1);

namespace App\Test\TestCase\Controller\REST1;

use App\Controller\REST1\MarksController;
use App\Model\Entity\Mark;
use App\Model\Entity\MarkForm;
use App\Model\Entity\MarkFormProperty;
use App\Model\Entity\Tree;
use App\Model\Table\MarkFormPropertiesTable;
use App\Model\Table\MarksTable;
use App\Test\TestCase\Controller\Shared\MarksControllerTestTrait;
use App\Test\Util\AjaxTrait;
use App\Test\Util\AuthenticateTrait;
use App\Test\Util\DependsOnFixtureTrait;
use App\Test\Util\ExperimentSiteTrait;
use Cake\Core\Configure;
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
                    $value = $faker->numberBetween(
                        $property->number_constraints->min,
                        $property->number_constraints->max
                    );
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
                'value' => $value,
                'exceptional_mark' => false,
                'mark_form_property_id' => $property->id
            ];
        }

        $data = [
            'date' => '2021-09-19T00:00:00.000Z',
            'author' => 'Hugo',
            'mark_form_id' => $form->id,
            'tree_id' => $tree->id,
            'variety_id' => null,
            'batch_id' => null,
            'mark_values' => $mark_values
        ];

        $this->enableCsrfToken();

        $this->post(self::ENDPOINT . '/add', ['data' => $data]);

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

    public function testAddPhotoFile__and__Add(): void
    {
        /**
         * upload image
         */
        $faker = \Faker\Factory::create();
        $imgPath = $faker->image(null, 200, 200);

        $image = new \Laminas\Diactoros\UploadedFile(
            $imgPath,
            filesize($imgPath),
            \UPLOAD_ERR_OK,
            'blob',
            'image/jpeg'
        );

        $this->configRequest(
            [
                'files' => [
                    'data' => $image,
                ],
            ]
        );

        $postData = [
            'filename' => 'test.jpg',
            'offset' => 0
        ];

        $this->enableCsrfToken();
        $this->post(self::ENDPOINT . '/add-photo-file', $postData);

        // clear this again, so it doesn't interfere with the next request below
        $this->_request['files'] = null;

        $this->assertResponseOk();
        /** @noinspection PhpUnhandledExceptionInspection */
        $resp = json_decode((string)$this->_response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $filename = $resp['data']['filename'];

        self::assertArrayHasKey('data', $resp);
        self::assertArrayHasKey('filename', $resp['data']);

        $uploadedFile = Configure::read('App.paths.uploadTmp') . DS . $filename;
        $this->assertFileEquals($imgPath, $uploadedFile);

        /**
         * Add mark
         */
        // prepare form with photo property
        /** @var MarkFormPropertiesTable $property */
        $property = $this->getTable('MarkFormProperties');
        $photoProperty = $property->find()->where(['field_type' => 'PHOTO'])->first();
        if (!$photoProperty) {
            $photoProperty = $property->newEmptyEntity();
            $photoProperty->name = 'photo';
            $photoProperty->validation_rule = '';
            $photoProperty->field_type = 'PHOTO';
            $photoProperty->mark_form_property_type = $this->getTable('MarkFormPropertyTypes')
                ->find()
                ->firstOrFail();
            $photoProperty->tree_property = true;
            $photoProperty->variety_property = false;
            $photoProperty->batch_property = false;
            $property->save($photoProperty);
        }

        /** @var MarkForm $form */
        $form = $this->getTable('MarkForms')
            ->find()
            ->contain(['MarkFormFields', 'MarkFormFields.MarkFormProperties'])
            ->matching('MarkFormFields', function ($q) {
                return $q->where(['MarkFormFields.mark_form_id = MarkForms.id']);
            })
            ->firstOrFail();

        $properties = array_map(static fn($field) => $field->mark_form_property_id, $form->mark_form_fields);
        if (!in_array($photoProperty->id, $properties, true)) {
            $form->mark_form_fields[] = $photoProperty;
            $this->getTable('MarkForms')->saveOrFail($form);
        }

        // get tree to mark
        /** @var Tree $tree */
        $tree = $this->getTable('Trees')
            ->find()
            ->firstOrFail();

        // actually add the photo
        $mark_values[] = [
            'value' => $filename,
            'exceptional_mark' => false,
            'mark_form_property_id' => $photoProperty->id
        ];

        $data = [
            'date' => '2021-09-19T00:00:00.000Z',
            'author' => 'Hugo',
            'mark_form_id' => $form->id,
            'tree_id' => $tree->id,
            'variety_id' => null,
            'batch_id' => null,
            'mark_values' => $mark_values
        ];

        $this->enableCsrfToken();
        $this->post(self::ENDPOINT . '/add', ['data' => $data]);
        $this->assertResponseSuccess();

        /** @var Mark $mark */
        $mark = $this->getTable('Marks')
            ->find()
            ->contain(['MarkValues'])
            ->last();

        $finalFilename = $mark->mark_values[0]->value;
        $pathFinalFile = Configure::read('App.paths.photos')
            . DS . substr($finalFilename, 0, 2)
            . DS . $finalFilename;

        $this->assertFileEquals($imgPath, $pathFinalFile);
    }

    public function testAddPhotoFile__multiChunk(): void
    {
        $faker = \Faker\Factory::create();
        $imgPath = $faker->image();
        $imgStream = fopen($imgPath, 'rb');

        $imgSize = filesize($imgPath);
        $chunk1Size = (int)ceil($imgSize / 2);
        $chunk2Size = $imgSize - $chunk1Size;

        $chunk1 = tmpfile();
        $chunk2 = tmpfile();

        stream_copy_to_stream($imgStream, $chunk1, $chunk1Size);
        stream_copy_to_stream($imgStream, $chunk2);

        $image1 = new \Laminas\Diactoros\UploadedFile(
            $chunk1,
            $chunk1Size,
            \UPLOAD_ERR_OK,
            'blob',
            'image/jpeg'
        );
        $image2 = new \Laminas\Diactoros\UploadedFile(
            $chunk2,
            $chunk2Size,
            \UPLOAD_ERR_OK,
            'blob',
            'image/jpeg'
        );

        // first chunk
        $this->configRequest(
            [
                'files' => [
                    'data' => $image1,
                ],
            ]
        );
        $postData = [
            'filename' => 'test.jpg',
            'offset' => 0
        ];
        $this->enableCsrfToken();
        $this->post(self::ENDPOINT . '/add-photo-file', $postData);
        $this->assertResponseOk();

        // second chunk
        $this->configRequest(
            [
                'files' => [
                    'data' => $image2,
                ],
            ]
        );
        $postData = [
            'filename' => 'test.jpg',
            'offset' => $chunk1Size
        ];
        $this->enableCsrfToken();
        $this->post(self::ENDPOINT . '/add-photo-file', $postData);
        $this->assertResponseOk();

        /** @noinspection PhpUnhandledExceptionInspection */
        $resp = json_decode((string)$this->_response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('data', $resp);
        self::assertArrayHasKey('filename', $resp['data']);

        $uploadedFile = Configure::read('App.paths.uploadTmp') . DS . $resp['data']['filename'];
        $this->assertFileEquals($imgPath, $uploadedFile);
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
