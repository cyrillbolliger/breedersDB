<?php
declare(strict_types=1);

namespace App\Controller\REST1;

use App\Controller\REST1Controller;
use App\Domain\Upload\ChunkUploadStrategy;
use App\Domain\Upload\UploadException;
use App\Domain\Upload\UploadStrategy;
use App\Model\Table\MarkValuesTable;
use Cake\Http\Response;

/**
 * Marks Controller
 *
 * @property \App\Model\Table\MarksTable $Marks
 * @method \App\Model\Entity\Mark[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class MarksController extends REST1Controller
{
    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        if (!$this->request->is('post')) {
            return $this->response
                ->withStatus(405)
                ->withAddedHeader('Allow', 'POST');
        }

        $mark = $this->Marks->newEmptyEntity();
        $mark = $this->Marks->patchEntity(
            $mark,
            $this->request->getData('data'),
            ['associated' => 'MarkValues']
        );

        if (!$this->Marks->save($mark)) {
            return $this->respondWithErrorJson(['photo upload' => $mark->getErrors()], 422);
        }

        $this->set('data', ['id' => $mark->id]);
    }

    /**
     * Add Photo method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function addPhotoFile()
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

    private function respondWithErrorJson(array $errors, int $statusCode): Response
    {
        return $this->response
            ->withStringBody(
                json_encode(
                    ['errors' => $errors],
                    JSON_THROW_ON_ERROR
                )
            )
            ->withStatus($statusCode);
    }
}
