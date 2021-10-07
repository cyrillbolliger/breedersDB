<?php
declare(strict_types=1);

namespace App\Controller\REST1;

use App\Controller\REST1Controller;

/**
 * Marks Controller
 *
 * @property \App\Model\Table\MarksTable $Marks
 * @method \App\Model\Entity\Mark[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 * @method \Cake\Http\Response respondWithErrorJson(array $errors, int $statusCode)
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
            return $this->respondWithErrorJson($mark->getErrors(), 422);
        }

        $this->set('data', ['id' => $mark->id]);
    }
}
