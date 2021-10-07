<?php
declare(strict_types=1);

namespace App\Controller\REST1;

use App\Controller\REST1Controller;
use App\Domain\Upload\ChunkUploadStrategy;
use App\Domain\Upload\UploadException;
use App\Domain\Upload\UploadStrategy;
use App\Model\Table\MarkValuesTable;

/**
 * Photos Controller
 *
 * @method \Cake\Http\Response respondWithErrorJson(array $errors, int $statusCode)
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
            return $this->respondWithErrorJson(['photo upload' => 'missing filename'], 422);
        }

        $file = $this->getRequest()->getUploadedFile('data');

        if ($file === null) {
            return $this->respondWithErrorJson(['photo upload' => 'missing file'], 422);
        }

        $offset = (int)$this->getRequest()->getData('offset');
        $tmpFileName = UploadStrategy::getTmpFileName(
            $origFileName,
            $this->getRequest()->getSession()->id()
        );

        try{
            $uploadHandler = new ChunkUploadStrategy(MarkValuesTable::ALLOWED_PHOTO_EXT, $tmpFileName);
            $uploadHandler->storeTmp($file, $offset);
        } catch (UploadException $e) {
            return $this->respondWithErrorJson(['photo upload' => $e->getMessage()], $e->getCode());
        }

        $this->set('data', ['filename' => $tmpFileName]);
    }
}
