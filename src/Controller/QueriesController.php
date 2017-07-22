<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Queries Controller
 *
 * @property \App\Model\Table\QueriesTable $Queries
 */
class QueriesController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
        $this->loadModel('QueryGroups');
        $queryGroups = $this->QueryGroups->find('all')->contain('Queries')->order('code');
        
        $this->set(compact('queryGroups'));
        $this->set('_serialize', ['queryGroups']);
    }

    /**
     * View method
     *
     * @param string|null $id Query id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $query = $this->Queries->get($id, [
            'contain' => []
        ]);

        $this->set('query', $query);
        $this->set('_serialize', ['query']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $query = $this->Queries->newEntity();
        if ($this->request->is('post')) {
            $query = $this->Queries->patchEntity($query, $this->request->data);
            if ($this->Queries->save($query)) {
                $this->Flash->success(__('The query has been saved.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The query could not be saved. Please, try again.'));
            }
        }
        
        $query_groups = $this->Queries->QueryGroups->find('list')->order('code');
        $this->set(compact('query', 'query_groups'));
        $this->set('_serialize', ['query', 'query_groups']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Query id.
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $query = $this->Queries->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $query = $this->Queries->patchEntity($query, $this->request->data);
            if ($this->Queries->save($query)) {
                $this->Flash->success(__('The query has been saved.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The query could not be saved. Please, try again.'));
            }
        }
        $query_groups = $this->Queries->QueryGroups->find('list')->order('code');
        $this->set(compact('query', 'query_groups'));
        $this->set('_serialize', ['query', 'query_groups']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Query id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $query = $this->Queries->get($id);
        if ($this->Queries->delete($query)) {
            $this->Flash->success(__('The query has been deleted.'));
        } else {
            $this->Flash->error(__('The query could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
