<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Exception\Exception;

/**
 * Crossings Controller
 *
 * @property \App\Model\Table\CrossingsTable $Crossings
 */
class CrossingsController extends AppController
{
    public $paginate = [
        'order' => ['modified' => 'desc'],
    ];
    
    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
        $this->paginate['contain'] = ['MotherTrees'];
        $crossings = $this->paginate($this->Crossings);

        $this->set(compact('crossings'));
        $this->set('_serialize', ['crossings']);
    }

    /**
     * View method
     *
     * @param string|null $id Crossing id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $crossing = $this->Crossings->get($id, [
            'contain' => ['Varieties', 'MotherTrees', 'Batches', 'MotherTrees.Trees']
        ]);

        $this->set('crossing', $crossing);
        $this->set('_serialize', ['crossing']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $crossing = $this->Crossings->newEntity();
        $mother_varieties = array();
        $father_varieties = array();
        $trees = array();
        
        if ($this->request->is('post')) {
            $crossing = $this->Crossings->patchEntity($crossing, $this->request->data);
            if ($this->Crossings->save($crossing)) {
                $this->Flash->success(__('The crossing has been saved.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The crossing could not be saved. Please try again.'));
                
                if ($crossing->mother_variety_id) {
                    $mother_varieties = $this->Crossings->Varieties->getConvarList($crossing->mother_variety_id);
                }
                if ($crossing->father_variety_id) {
                    $father_varieties = $this->Crossings->Varieties->getConvarList($crossing->father_variety_id);
                }
                if ($crossing->mother_tree_id) {
                    $trees = $this->Crossings->Trees->getIdPublicidAndConvarList($crossing->mother_tree_id);
                }
            }
        }
        
        $this->set(compact('crossing', 'mother_varieties', 'father_varieties', 'trees'));
        $this->set('_serialize', ['crossing']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Crossing id.
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $crossing = $this->Crossings->get($id, [
            'contain' => []
        ]);
        
        $mother_varieties = array();
        $father_varieties = array();
        $trees = array();
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $crossing = $this->Crossings->patchEntity($crossing, $this->request->data);
            if ($this->Crossings->save($crossing)) {
                $this->Flash->success(__('The crossing has been saved.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The crossing could not be saved. Please, try again.'));
            }
        }
                
        if ($crossing->mother_variety_id) {
            $mother_varieties = $this->Crossings->Varieties->getConvarList($crossing->mother_variety_id);
        }
        if ($crossing->father_variety_id) {
            $father_varieties = $this->Crossings->Varieties->getConvarList($crossing->father_variety_id);
        }
        if ($crossing->mother_tree_id) {
            $trees = $this->Crossings->Trees->getIdPublicidAndConvarList($crossing->mother_tree_id);
        }
        
        $this->set(compact('crossing', 'mother_varieties', 'father_varieties', 'trees'));
        $this->set('_serialize', ['crossing']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Crossing id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $crossing = $this->Crossings->get($id);
        if ($this->Crossings->delete($crossing)) {
            $this->Flash->success(__('The crossing has been deleted.'));
        } else {
            $this->Flash->error(__('The crossing could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
    
    /**
     * Return filtered index table
     */
    public function filter() {
        $allowed_fields = ['code'];
                
        if ( $this->request->is('get') 
                && $this->request->is('ajax') 
                && ! empty($this->request->query['fields'])
                && array_intersect($allowed_fields, $this->request->query['fields']))
        {             
            $entries = $this->Crossings->filterCodes($this->request->query['term']);
            
            if ( ! empty($this->request->query['sort']) ) {
                $sort = $this->request->query['sort'];
                $direction = empty($this->request->query['direction']) ? 'asc' : $this->request->query['direction'];
                $this->paginate['order'] = [ $sort => $direction ];
            }
        } else {
            throw new Exception(__('Direct access not allowed.'));
        }
        
        if ( $entries->count() ) {
            $crossings = $this->paginate($entries);
            $this->set(compact('crossings'));
            $this->set('_serialize', ['crossings']);
            $this->render('/Element/Crossing/index_table');
        } else {
            $this->render('/Element/nothing_found');
        }
    }
}
