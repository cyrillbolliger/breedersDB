<?php
declare(strict_types=1);

namespace App\Controller\REST1;

use App\Controller\AppController;

/**
 * MarkForms Controller
 *
 * @property \App\Model\Table\MarkFormsTable $MarkForms
 * @method \App\Model\Entity\MarkForm[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class MarkFormsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $data = $this->MarkForms
            ->find()
            ->orderAsc('name')
            ->all();
        $this->set(compact('data'));
        $this->viewBuilder()
             ->setClassName('Json')
             ->setOption('serialize', ['data']);
    }

    /**
     * View method
     *
     * @param  string|null  $id  Mark Form id.
     *
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        // todo

        $markForm = $this->MarkForms->get($id, [
            'contain' => ['MarkFormProperties', 'Marks'],
        ]);

        $this->set(compact('markForm'));
    }
}
