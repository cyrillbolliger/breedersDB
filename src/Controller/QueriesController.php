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
     *
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $query = $this->Queries->get($id, [
            'contain' => []
        ]);
        
        $query->query = json_decode($query->query);
        
        $q = $this->Queries->buildViewQuery($query->query);
        $results = $this->paginate($q);
        
        $tmp = $this->Queries->getViewQueryColumns($query->query);
        $columns = $this->Queries->getTranslatedFieldsFromList($tmp);
        
        $this->loadModel('QueryGroups');
        $queryGroups  = $this->QueryGroups->find('all')->contain('Queries')->order('code');
        $query_groups = $this->QueryGroups->find('list')->order('code');
        
        $this->set(compact('query', 'query_groups', 'queryGroups', 'results', 'columns'));
        $this->set('_serialize', ['query', 'query_groups', 'queryGroups', 'results', 'columns']);
    }
    
    /**
     * Add method
     *
     * @param int $query_group_id Query group the query will be added to
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     */
    public function add($query_group_id)
    {
        $query = $this->Queries->newEntity();
        if ($this->request->is('post')) {
            $query = $this->Queries->patchEntityWithQueryData($query, $this->request->data);
            if ($this->Queries->save($query)) {
                $this->Flash->success(__('The query has been saved.'));
                
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The query could not be saved. Please, try again.'));
            }
        }
        
        $views             = $this->Queries->getViewNames();
        $view_fields       = $this->Queries->getTranslatedFieldsOf(array_keys($views));
        $default_root_view = 'MarksView';
        $root_view         = $default_root_view;
        
        $associations = array();
        foreach (array_keys($views) as $view_name) {
            $associations[$view_name] = $this->Queries->getAssociationsOf($view_name);
        }
        
        $this->loadModel('QueryGroups');
        $queryGroups  = $this->QueryGroups->find('all')->contain('Queries')->order('code');
        $query_groups = $this->QueryGroups->find('list')->order('code');
        
        $this->set(compact(
            'default_root_view',
            'root_view',
            'query_group_id',
            'query',
            'query_groups',
            'queryGroups',
            'views',
            'view_fields',
            'associations'));
        $this->set('_serialize', [
            'default_root_view',
            'root_view',
            'query_group_id',
            'query',
            'query_groups',
            'queryGroups',
            'views',
            'view_fields',
            'associations'
        ]);
    }
    
    /**
     * Edit method
     *
     * @param string|null $id Query id.
     *
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $query = $this->Queries->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $query = $this->Queries->patchEntityWithQueryData($query, $this->request->data);
            if ($this->Queries->save($query)) {
                $this->Flash->success(__('The query has been saved.'));
                
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The query could not be saved. Please, try again.'));
            }
        }
        
        $this->loadModel('QueryGroups');
        $queryGroups  = $this->QueryGroups->find('all')->contain('Queries')->order('code');
        $query_groups = $this->QueryGroups->find('list')->order('code');
        
        $this->set(compact('query', 'query_groups', 'queryGroups'));
        $this->set('_serialize', ['query', 'query_groups', 'queryGroups']);
    }
    
    /**
     * Delete method
     *
     * @param string|null $id Query id.
     *
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
