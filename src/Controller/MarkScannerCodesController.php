<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * MarkScannerCodes Controller
 *
 * @property \App\Model\Table\MarkScannerCodesTable $MarkScannerCodes
 */
class MarkScannerCodesController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['MarkFormProperties']
        ];
        $markScannerCodes = $this->paginate($this->MarkScannerCodes);
        $properties = $this->MarkScannerCodes->MarkFormProperties
                ->find('list')
                ->order(['name'=>'asc'])
                ->toArray();
        
        $all = [0 => __('(all)')];
        $mark_form_properties = $all + $properties;

        $this->set(compact('markScannerCodes','mark_form_properties'));
        $this->set('_serialize', ['markScannerCodes']);
    }

    /**
     * View method
     *
     * @param string|null $id Mark Scanner Code id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $markScannerCode = $this->MarkScannerCodes->get($id, [
            'contain' => ['MarkFormProperties']
        ]);

        $this->set('markScannerCode', $markScannerCode);
        $this->set('_serialize', ['markScannerCode']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $markScannerCode = $this->MarkScannerCodes->newEntity();
        $this->request->data['code'] = $this->MarkScannerCodes->getNextFreeCode();
        if ($this->request->is('post')) {
            $markScannerCode = $this->MarkScannerCodes->patchEntity($markScannerCode, $this->request->data);
            if ($this->MarkScannerCodes->save($markScannerCode)) {
                $property = $this->MarkScannerCodes->MarkFormProperties->get($markScannerCode->mark_form_property_id);
                $this->Flash->success(
                        __(
                            '{0}: {1} has been saved.',
                            '<strong>'.h($property->name).'</strong>',
                            '<strong>'.h($markScannerCode->mark_value).'</strong>'
                        ),
                        ['escape'=>false]
                );

                return $this->redirect(['action' => 'print', $markScannerCode->id, 'add']);
            } else {
                $this->Flash->error(__('The mark scanner code could not be saved. Please, try again.'));
            }
        }
        $markFormProperties = $this->MarkScannerCodes->MarkFormProperties->find('list');
        $this->set(compact('markScannerCode', 'markFormProperties'));
        $this->set('_serialize', ['markScannerCode']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Mark Scanner Code id.
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $markScannerCode = $this->MarkScannerCodes->get($id, [
            'contain' => ['MarkFormProperties']
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $markScannerCode = $this->MarkScannerCodes->patchEntity($markScannerCode, $this->request->data);
            if ($this->MarkScannerCodes->save($markScannerCode)) {
                $this->Flash->success(__('The mark scanner code has been saved.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The mark scanner code could not be saved. Please, try again.'));
            }
        }
        $markFormProperties = $this->MarkScannerCodes->MarkFormProperties->find('list');
        $this->set(compact('markScannerCode', 'markFormProperties'));
        $this->set('_serialize', ['markScannerCode']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Mark Scanner Code id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $markScannerCode = $this->MarkScannerCodes->get($id);
        if ($this->MarkScannerCodes->delete($markScannerCode)) {
            $this->Flash->success(__('The mark scanner code has been deleted.'));
        } else {
            $this->Flash->error(__('The mark scanner code could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
    
    /**
     * Return filtered index table
     */
    public function filter() {
        $allowed_fields = ['mark_form_property_id'];
                
        if ( $this->request->is('get') 
                && $this->request->is('ajax') 
                && ! empty($this->request->query['fields'])
                && array_intersect($allowed_fields, $this->request->query['fields']))
        {             
            $entries = $this->MarkScannerCodes->filter($this->request->query['term']);
            
            if ( ! empty($this->request->query['sort']) ) {
                $sort = $this->request->query['sort'];
                $direction = empty($this->request->query['direction']) ? 'asc' : $this->request->query['direction'];
                $this->paginate['order'] = [ $sort => $direction ];
            }
            if ( ! empty($this->request->query['page']) ) {
                $this->paginate['page'] = $this->request->query['page'];
            }
            
        } else {
            throw new Exception(__('Direct access not allowed.'));
        }
        
        if ( $entries ) {
            $markScannerCodes = $this->paginate($entries);
            $this->set(compact('markScannerCodes'));
            $this->set('_serialize', ['markScannerCodes']);
            $this->render('/Element/MarkScannerCode/index_table');
        } else {
            $this->render('/Element/nothing_found');
        }
    }
    
    public function getMark() {
        if ( $this->request->is('get') && $this->request->is('ajax') ) {
            $entries = $this->MarkScannerCodes->find()
                ->select(['mark_value', 'mark_form_property_id'])
                ->where(['code' => $this->request->query['term']])
                ->first()
                ->toArray();
        } else {
            throw new Exception(__('Direct access not allowed.'));
        }
        
        $this->set(['data' => $entries]);
        $this->render('/Element/ajaxreturn');
    }
    
    /**
     * Show the print dialog
     *
     * @param int $id
     * @param string $caller action to redirect after printing
     * @param mixed $params for action
     */
    public function print(int $id, string $caller, $params = null) {
        $zpl = $this->MarkScannerCodes->getLabelZpl($id);
        
        $this->set([
            'zpl' => $zpl,
            'controller' => 'MarkScannerCodes',
            'action' => $caller,
            'params' => $params,
            'nav' => 'Mark/nav'
        ]);
        $this->render('/Element/print');
    }
    
    /**
     * Show the print dialog
     *
     * @param int $id
     * @param string $caller action to redirect after printing
     * @param mixed $params for action
     */
    public function printSubmit() {
        $zpl = $this->MarkScannerCodes->getSubmitLabelZpl();
        
        $this->set([
            'zpl' => $zpl,
            'controller' => 'MarkScannerCodes',
            'action' => 'index',
            'params' => null,
            'nav' => 'Mark/nav'
        ]);
        $this->render('/Element/print');
    }
}
