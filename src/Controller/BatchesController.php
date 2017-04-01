<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Exception\Exception;

/**
 * Batches Controller
 *
 * @property \App\Model\Table\BatchesTable $Batches
 */
class BatchesController extends AppController
{
    public $paginate = [
        'order' => ['modified' => 'desc'],
    ];
    
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('MarksReader');
    }
    
    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
        $this->paginate['contain'] = ['Crossings'];
        $batches = $this->paginate($this->Batches);

        $this->set(compact('batches'));
        $this->set('_serialize', ['batches']);
    }

    /**
     * View method
     *
     * @param string|null $id Batch id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $batch = $this->Batches->get($id, [
            'contain' => ['Crossings', 'Marks', 'Varieties']
        ]);
        
        $marks = $this->MarksReader->get(null, null, $id);
        $this->set('marks', $marks);
        $this->set('batch', $batch);
        $this->set('_serialize', ['batch']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $batch = $this->Batches->newEntity();
        if ($this->request->is('post')) {
            $batch = $this->Batches->patchEntity($batch, $this->request->data);
            if ($this->Batches->save($batch)) {
                $this->Flash->success(__('The batch has been saved.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The batch could not be saved. Please, try again.'));
            }
        }
        $crossings = $this->Batches->Crossings->find('list')->where(['id <>'=>1]);
        $this->set(compact('batch', 'crossings'));
        $this->set('_serialize', ['batch']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Batch id.
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $batch = $this->Batches->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $batch = $this->Batches->patchEntity($batch, $this->request->data);
            if ($this->Batches->save($batch)) {
                $this->Flash->success(__('The batch has been saved.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The batch could not be saved. Please, try again.'));
            }
        }
        $crossings = $this->Batches->Crossings->find('list')->where(['id <>'=>1]);
        $this->set(compact('batch', 'crossings'));
        $this->set('_serialize', ['batch']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Batch id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $batch = $this->Batches->get($id);
        if ($this->Batches->delete($batch)) {
            $this->Flash->success(__('The batch has been deleted.'));
        } else {
            $this->Flash->error(__('The batch could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
    
    /**
     * Return filtered index table
     */
    public function filter() {
        $allowed_fields = ['crossing_batch'];
                
        if ( $this->request->is('get') 
                && $this->request->is('ajax') 
                && ! empty($this->request->query['fields'])
                && array_intersect($allowed_fields, $this->request->query['fields']))
        {             
            $entries = $this->Batches->filterCrossingBatches($this->request->query['term']);
            
            if ( ! empty($this->request->query['sort']) ) {
                $sort = $this->request->query['sort'];
                $direction = empty($this->request->query['direction']) ? 'asc' : $this->request->query['direction'];
                $this->paginate['order'] = [ $sort => $direction ];
            }
        } else {
            throw new Exception(__('Direct access not allowed.'));
        }
        
        if ( $entries ) {
            $batches = $this->paginate($entries);
            $this->set(compact('batches'));
            $this->set('_serialize', ['batches']);
            $this->render('/Element/Batch/index_table');
        } else {
            $this->render('/Element/nothing_found');
        }
    }
}
