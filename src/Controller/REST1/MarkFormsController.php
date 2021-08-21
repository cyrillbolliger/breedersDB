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
    public function beforeRender(\Cake\Event\EventInterface $event)
    {
        $this->viewBuilder()
             ->setClassName('Json')
             ->setOption('serialize', ['data']);

        parent::beforeRender($event);
    }

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
        $this->set('data', $data);
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
        $data = $this->MarkForms->get($id, [
            'contain' => [
                'MarkFormProperties' => [
                    'sort' => ['MarkFormFields.priority' => 'ASC']
                ]
            ],
        ])->toArray();

        foreach($data['mark_form_properties'] as $key => $value){
            unset(
                $data['mark_form_properties'][$key]['_joinData'],
                $data['mark_form_properties'][$key]['validation_rule']
            );
        }

        $this->set('data', $data);
    }
}
