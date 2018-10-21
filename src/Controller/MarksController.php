<?php

namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Exception\Exception;
use Cake\Event\Event;

/**
 * Marks Controller
 *
 * @property \App\Model\Table\MarksTable $Marks
 */
class MarksController extends AppController {
	
	public $paginate = [
		'order' => [ 'modified' => 'desc' ],
		'limit' => 100,
	];
	
	public function initialize() {
		parent::initialize();
		$this->loadComponent( 'Brain' );
		$this->loadComponent( 'MarksReader' );
	}
	
	public function beforeFilter( Event $event ) {
		parent::beforeFilter( $event );
		
		// since we add fields dynamically, we have to unlock them in the security component
		$this->unlockDynamicallyAddedFields();
	}
	
	/**
	 * Unlock the dynamically added fields in the security component
	 */
	public function unlockDynamicallyAddedFields() {
		if ( ! empty( $this->request->data['mark_form_fields']['mark_form_properties'] ) ) {
			$ids = array_keys( $this->request->data['mark_form_fields']['mark_form_properties'] );
			foreach ( $ids as $id ) {
				$this->Security->config( 'unlockedFields',
					[ 'mark_form_fields.mark_form_properties.' . $id . '.mark_values.value' ] );
			}
		}
	}
	
	/**
	 * Index method
	 *
	 * @return \Cake\Network\Response|null
	 */
	public function index() {
		$this->paginate['contain'] = [
			'Trees',
			'Varieties',
			'Varieties.Batches',
			'Batches',
			'Batches.Crossings' => [ 'joinType' => 'LEFT' ],
			'MarkValues',
			'MarkValues.MarkFormProperties'
		];
		$marks                     = $this->paginate( $this->Marks );
		
		$types = $this->Marks->MarkValues->MarkFormProperties->MarkFormPropertyTypes
			->find( 'list' )
			->order( [ 'name' => 'asc' ] )
			->toArray();
		
		$all[0]                   = '(all)'; // dont translate, because of bug in cakephp
		$mark_form_property_types = $all + $types;
		
		$this->set( compact( 'marks', 'mark_form_property_types' ) );
		$this->set( '_serialize', [ 'marks' ] );
	}
	
	/**
	 * View method
	 *
	 * @param string|null $id Mark id.
	 *
	 * @return \Cake\Network\Response|null
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function view( $id = null ) {
		$mark = $this->Marks->get( $id, [
			'contain' => [
				'MarkForms',
				'Trees',
				'Varieties',
				'Varieties.Batches',
				'Batches',
				'Batches.Crossings' => [ 'joinType' => 'LEFT' ],
				'MarkValues',
				'MarkValues.MarkFormProperties'
			]
		] );
		
		$this->set( 'mark', $mark );
		$this->set( '_serialize', [ 'mark' ] );
	}
	
	/**
	 * Add method
	 *
	 * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
	 */
	public function addTreeMarkByScanner() {
		$this->addTreeMark();
	}
	
	/**
	 * Add method
	 *
	 * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
	 */
	public function addTreeMark() {
		$mark = $this->Marks->newEntity();
		if ( $this->request->is( 'post' ) ) {
			$data = $this->Marks->prepareToSaveAssociated( $this->request->data );
			$mark = $this->Marks->patchEntity( $mark, $data, [ 'associated' => 'MarkValues' ] );
			$mark->dirty( 'mark_values', true );
			if ( $this->Marks->save( $mark ) ) {
				$this->Flash->success( __( 'The mark has been saved.' ) );
				
				return $this->redirect( $this->referer() );
			} else {
				$this->Flash->error( __( 'The mark could not be saved.' ) );
				$errors = $mark->errors();
				if ( ! empty( $errors['tree_id'] ) ) {
					$this->Flash->error( __( 'Please select a tree by public id.' ) );
				}
				if ( ! empty( $errors['mark_values'] ) ) {
					$this->Flash->error( __( 'One of the mark values violates the validation rules. Please check data types.' ) );
				}
			}
		}
		
		$mark = $this->Brain->remember( $mark );
		
		if ( ! empty( $mark->mark_form_id ) ) {
			$markFormFields = $this->Marks->MarkForms->MarkFormFields->find()
			                                                         ->contain( [ 'MarkFormProperties' ] )
			                                                         ->where( [ 'mark_form_id' => $mark->mark_form_id ] )
			                                                         ->order( [ 'priority' => 'asc' ] );
		} else {
			$markFormFields = null;
		}
		
		$markForms          = $this->Marks->MarkForms->find( 'list' );
		$markFormProperties = $this->Marks->MarkForms->MarkFormFields->MarkFormProperties->find( 'list' );
		$this->set( compact( 'mark', 'markForms', 'markFormProperties', 'markFormFields' ) );
		$this->set( '_serialize', [ 'mark' ] );
	}
	
	/**
	 * Add method
	 *
	 * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
	 */
	public function addVarietyMark() {
		$mark      = $this->Marks->newEntity();
		$varieties = array();
		if ( $this->request->is( 'post' ) ) {
			$data = $this->Marks->prepareToSaveAssociated( $this->request->data );
			$mark = $this->Marks->patchEntity( $mark, $data, [ 'associated' => 'MarkValues' ] );
			$mark->dirty( 'mark_values', true );
			if ( $this->Marks->save( $mark ) ) {
				$this->Flash->success( __( 'The mark has been saved.' ) );
				
				return $this->redirect( [ 'action' => 'addVarietyMark' ] );
			} else {
				$this->Flash->error( __( 'The mark could not be saved.' ) );
				$errors = $mark->errors();
				if ( ! empty( $errors['variety_id'] ) ) {
					$this->Flash->error( __( 'Please select a variety.' ) );
				}
				if ( ! empty( $errors['mark_values'] ) ) {
					$this->Flash->error( __( 'One of the mark values violates the validation rules. Please check data types.' ) );
				}
				
				if ( $mark->variety_id ) {
					$varieties = $this->Marks->Varieties->getConvarList( $mark->variety_id );
				}
			}
		}
		
		$mark = $this->Brain->remember( $mark );
		
		if ( ! empty( $mark->mark_form_id ) ) {
			$markFormFields = $this->Marks->MarkForms->MarkFormFields->find()
			                                                         ->contain( [ 'MarkFormProperties' ] )
			                                                         ->where( [ 'mark_form_id' => $mark->mark_form_id ] )
			                                                         ->order( [ 'priority' => 'asc' ] );
		} else {
			$markFormFields = null;
		}
		
		$markForms          = $this->Marks->MarkForms->find( 'list' );
		$markFormProperties = $this->Marks->MarkForms->MarkFormFields->MarkFormProperties->find( 'list' );
		$this->set( compact( 'mark', 'varieties', 'markForms', 'markFormProperties', 'markFormFields' ) );
		$this->set( '_serialize', [ 'mark' ] );
	}
	
	/**
	 * Add method
	 *
	 * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
	 */
	public function addBatchMark() {
		$mark    = $this->Marks->newEntity();
		$batches = array();
		if ( $this->request->is( 'post' ) ) {
			$data = $this->Marks->prepareToSaveAssociated( $this->request->data );
			$mark = $this->Marks->patchEntity( $mark, $data, [ 'associated' => 'MarkValues' ] );
			$mark->dirty( 'mark_values', true );
			if ( $this->Marks->save( $mark ) ) {
				$this->Flash->success( __( 'The mark has been saved.' ) );
				
				return $this->redirect( [ 'action' => 'addBatchMark' ] );
			} else {
				$this->Flash->error( __( 'The mark could not be saved.' ) );
				$errors = $mark->errors();
				if ( ! empty( $errors['batch_id'] ) ) {
					$this->Flash->error( __( 'Please select a batch.' ) );
				}
				if ( ! empty( $errors['mark_values'] ) ) {
					$this->Flash->error( __( 'One of the mark values violates the validation rules. Please check data types.' ) );
				}
				
				$batches = $this->Marks->Batches->getCrossingBatchList( $mark->batch_id );
			}
		}
		
		$mark = $this->Brain->remember( $mark );
		
		if ( ! empty( $mark->mark_form_id ) ) {
			$markFormFields = $this->Marks->MarkForms->MarkFormFields->find()
			                                                         ->contain( [ 'MarkFormProperties' ] )
			                                                         ->where( [ 'mark_form_id' => $mark->mark_form_id ] )
			                                                         ->order( [ 'priority' => 'asc' ] );
		} else {
			$markFormFields = null;
		}
		
		$markForms          = $this->Marks->MarkForms->find( 'list' );
		$markFormProperties = $this->Marks->MarkForms->MarkFormFields->MarkFormProperties->find( 'list' );
		$this->set( compact( 'mark', 'markForms', 'markFormProperties', 'markFormFields', 'batches' ) );
		$this->set( '_serialize', [ 'mark' ] );
	}
	
	/**
	 * Edit method
	 *
	 * @param string|null $id Mark id.
	 *
	 * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Network\Exception\NotFoundException When record not found.
	 */
	public function edit( $id = null ) {
		$mark = $this->Marks->get( $id, [
			'contain' => [
				'MarkForms',
				'Trees',
				'Trees.Rows',
				'Trees.ExperimentSites',
				'Trees.Varieties',
				'Varieties',
				'Varieties.Batches',
				'Varieties.Batches.Crossings',
				'Batches',
				'Batches.Crossings',
			]
		] );
		
		if ( $this->request->is( [ 'patch', 'post', 'put' ] ) ) {
			$mark = $this->Marks->patchEntity( $mark, $this->request->data );
			if ( $this->Marks->save( $mark ) ) {
				$this->Flash->success( __( 'The mark has been saved.' ) );
				
				return $this->redirect( [ 'action' => 'index' ] );
			} else {
				$this->Flash->error( __( 'The mark could not be saved.' ) );
			}
		}
		
		$marks              = $this->MarksReader->get( $mark->tree_id, $mark->variety_id, $mark->batch_id, $id );
		$markForms          = $this->Marks->MarkForms->find( 'list' );
		$markFormProperties = $this->Marks->MarkForms->MarkFormFields->MarkFormProperties->find( 'list' );
		$this->set( compact( 'mark', 'markForms', 'markFormProperties', 'markFormFields', 'marks' ) );
		$this->set( '_serialize', [ 'mark' ] );
	}
	
	/**
	 * Delete method
	 *
	 * @param string|null $id Mark id.
	 *
	 * @return \Cake\Network\Response|null Redirects to index.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function delete( $id = null ) {
		$this->request->allowMethod( [ 'post', 'delete' ] );
		$mark = $this->Marks->get( $id );
		if ( $this->Marks->delete( $mark ) ) {
			$this->Flash->success( __( 'The mark has been deleted.' ) );
		} else {
			$this->Flash->error( __( 'The mark could not be deleted. Please, try again.' ) );
		}
		
		return $this->redirect( [ 'action' => 'index' ] );
	}
	
	/**
	 * Display form fields of form with the given id
	 *
	 * @param int $id
	 */
	public function getFormFields( $id = null ) {
		if ( ! empty( $id ) ) {
			$markFormFields = $this->Marks->MarkForms->MarkFormFields->find()
			                                                         ->contain( [ 'MarkFormProperties' ] )
			                                                         ->where( [ 'mark_form_id' => $id ] )
			                                                         ->order( [ 'priority' => 'asc' ] );
		} else {
			$markFormFields = null;
		}
		
		$this->set( 'markFormFields', $markFormFields );
		$this->set( '_serialize', [ 'markFormFields' ] );
		
		$this->render( '/Element/Mark/fields' );
	}
	
	/**
	 * Return filtered index table
	 */
	public function filter() {
		$allowed_fields = [ 'mark_form_property_type_id' ];
		
		if ( $this->request->is( 'get' )
		     && $this->request->is( 'ajax' )
		     && ! empty( $this->request->query['fields'] )
		     && array_intersect( $allowed_fields, $this->request->query['fields'] )
		) {
			$entries = $this->Marks->filter( $this->request->query['term'] );
			
			if ( ! empty( $this->request->query['sort'] ) ) {
				$sort                    = $this->request->query['sort'];
				$direction               = empty( $this->request->query['direction'] ) ? 'asc' : $this->request->query['direction'];
				$this->paginate['order'] = [ $sort => $direction ];
			}
			if ( ! empty( $this->request->query['page'] ) ) {
				$this->paginate['page'] = $this->request->query['page'];
			}
			
		} else {
			throw new Exception( __( 'Direct access not allowed.' ) );
		}
		
		if ( $entries ) {
			$marks = $this->paginate( $entries );
			$this->set( compact( 'marks' ) );
			$this->set( '_serialize', [ 'marks' ] );
			$this->render( '/Element/Mark/index_table' );
		} else {
			$this->render( '/Element/nothing_found' );
		}
	}
}
