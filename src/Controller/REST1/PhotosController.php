<?php

declare(strict_types=1);

namespace App\Controller\REST1;

use App\Controller\Component\JsonResponseComponent;
use App\Controller\REST1Controller;
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

        // ensure the file exists and it is an image
        $dims = getimagesize($path);
        if (!$dims) {
            return $this->response
                ->withStatus(404);
        }

        [$imgWidth, $imgHeight] = $dims;

        $wantedWidth = (int)$this->getRequest()->getQuery('w', 0);
        $wantedHeight = (int)$this->getRequest()->getQuery('h', 0);

        // original image size wanted
        if (0 === $wantedWidth && 0 === $wantedHeight) {
            return $this->response->withFile($path);
        }

        if ($wantedWidth === 0) {
            $ratio = $wantedHeight/$imgHeight;
        } elseif ($wantedHeight === 0) {
            $ratio = $wantedWidth/$imgWidth;
        } else {
            $ratio = min($wantedWidth/$imgWidth, $wantedHeight/$imgHeight);
        }

        // wanted size is larger than actual image
        if ($ratio > 1) {
            return $this->response->withFile($path);
        }

        $reDimWidth = (int)round($imgWidth*$ratio);
        $reDimHeight = (int)round($imgHeight*$ratio);

        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $reDimPath = preg_replace("/\.${ext}$/", "-${reDimWidth}x${reDimHeight}.$ext", $path);

        // generate image with this dims, if it does not exist
        if (!file_exists($reDimPath)) {
            try {
                $imagick = new \Imagick($path);
                $imagick->setbackgroundcolor('transparent');
                $imagick->thumbnailImage($reDimWidth, $reDimHeight, true, true);
                $imagick->writeImage($reDimPath);
                $imagick->destroy();
            } catch (\ImagickException $e) {
                Log::error($e->getMessage());
                return $this->JsonResponse->respondWithErrorJson(['thumbnail' => 'resizing failed'], 500);
            }
        }

        return $this->response->withFile($reDimPath);
    }
}
