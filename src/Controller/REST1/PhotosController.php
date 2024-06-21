<?php

declare(strict_types=1);

namespace App\Controller\REST1;

use App\Controller\Component\JsonResponseComponent;
use App\Controller\REST1Controller;
use App\Domain\ImageEditor\ImageEditor;
use App\Domain\ImageEditor\ImageEditorException;
use App\Domain\Upload\ChunkUploadStrategy;
use App\Domain\Upload\UploadException;
use App\Domain\Upload\UploadStrategy;
use App\Model\Table\MarkValuesTable;
use Cake\Core\Configure;
use Cake\Log\Log;

/**
 * Photos Controller
 *
 * @property JsonResponseComponent $JsonResponse
 */
class PhotosController extends REST1Controller
{
    public function add()
    {
        if (!$this->request->is('post')) {
            return $this->response
                ->withStatus(405)
                ->withAddedHeader('Allow', 'POST');
        }

        $origFileName = $this->getRequest()->getData('filename');

        if (empty($origFileName)) {
            return $this->JsonResponse->respondWithErrorJson(['photo upload' => 'missing filename'], 422);
        }

        $file = $this->getRequest()->getUploadedFile('data');

        if ($file === null) {
            return $this->JsonResponse->respondWithErrorJson(['photo upload' => 'missing file'], 422);
        }

        $offset = (int)$this->getRequest()->getData('offset');
        $tmpFileName = UploadStrategy::getTmpFileName(
            $origFileName,
            $this->getRequest()->getSession()->id()
        );

        try {
            $uploadHandler = new ChunkUploadStrategy(MarkValuesTable::ALLOWED_PHOTO_EXT, $tmpFileName);
            $uploadHandler->storeTmp($file, $offset);
        } catch (UploadException $e) {
            return $this->JsonResponse->respondWithErrorJson(['photo upload' => $e->getMessage()], $e->getCode());
        }

        $this->set('data', ['filename' => $tmpFileName]);
    }

    public function view(string $filename): \Cake\Http\Response
    {
        // validate filename
        if (!preg_match('/^[a-zA-Z0-9.-]+$/', $filename) || str_contains($filename, '..')) {
            return $this->response
                ->withStatus(400);
        }

        $path = realpath(
            Configure::read('App.paths.photos')
            . DS . UploadStrategy::getSubdir($filename)
            . DS . $filename
        );

        if (!$path || !ImageEditor::isImage($path)) {
            return $this->response
                ->withStatus(404);
        }

        $width = (int)$this->getRequest()->getQuery('w', 0);
        $height = (int)$this->getRequest()->getQuery('h', 0);

        try {
            $imageEditor = new ImageEditor($path);
            $thumbPath = $imageEditor->getThumbnail($width, $height);
        } catch (ImageEditorException $e) {
            return $this->JsonResponse->respondWithErrorJson(['thumbnail' => $e->getMessage()], 500);
        }

        return $this->response->withFile($thumbPath);
    }
}
