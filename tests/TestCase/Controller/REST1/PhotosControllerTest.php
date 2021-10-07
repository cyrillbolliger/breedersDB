<?php

declare(strict_types=1);

namespace App\Test\TestCase\Controller\REST1;

use App\Domain\Upload\UploadStrategy;
use App\Test\Util\AjaxTrait;
use App\Test\Util\AuthenticateTrait;
use App\Test\Util\DependsOnFixtureTrait;
use App\Test\Util\ExperimentSiteTrait;
use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Cake\Utility\Security;

/**
 * App\Controller\REST1\PhotosController Test Case
 *
 * @uses \App\Controller\REST1\PhotosController
 */
class PhotosControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use DependsOnFixtureTrait;
    use AuthenticateTrait;
    use ExperimentSiteTrait;
    use AjaxTrait;

    private const ENDPOINT = '/api/1/photos';
    protected array $dependsOnFixture = [];

    protected function setUp(): void
    {
        $this->authenticate();
        $this->setSite();
        parent::setUp();
    }

    /**
     * Test view method
     *
     * @return void
     * @uses \App\Controller\REST1\PhotosController::view()
     */
    public function testView(): void
    {
        $faker = \Faker\Factory::create();
        $pathTmpFile = $faker->image();

        $ext = pathinfo($pathTmpFile, PATHINFO_EXTENSION);
        $finalFilename = substr(hash(Security::$hashType, Security::randomBytes(16), false), 0, 32) . '.' . $ext;
        $finalFileDir = Configure::read('App.paths.photos') . DS . UploadStrategy::getSubdir($finalFilename);
        $pathFinalFile = $finalFileDir . DS . $finalFilename;

        UploadStrategy::createDir($finalFileDir);
        rename($pathTmpFile, $pathFinalFile);

        $this->get(self::ENDPOINT . '/view/' . $finalFilename);
        $this->assertResponseOk();
        $this->assertFileResponse($pathFinalFile);
    }

    public function testAdd(): void
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
        $this->post(self::ENDPOINT . '/add', $postData);
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
        $this->post(self::ENDPOINT . '/add', $postData);
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
     * @uses \App\Controller\REST1\PhotosController::edit()
     */
    public function testEdit(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test delete method
     *
     * @return void
     * @uses \App\Controller\REST1\PhotosController::delete()
     */
    public function testDelete(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
