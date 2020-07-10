<?php

namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Exception\Exception;
use Cake\Event\Event;

/**
 * MarkForms Controller
 *
 * @property \App\Model\Table\MarkFormsTable $MarkForms
 */
class MarkFormsController extends AppController {
	public $paginate = [
		'order' => [ 'modified' => 'desc' ],
		'limit' => 100,
	];

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
		$markForms = $this->paginate( $this->MarkForms );

		$this->set( compact( 'markForms' ) );
		$this->set( '_serialize', [ 'markForms' ] );
	}

	/**
	 * View method
	 *
	 * @param string|null $id Mark Form id.
	 *
	 * @return \Cake\Network\Response|null
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function view( $id = null ) {
		$markForm = $this->MarkForms->get( $id, [
			'contain' => [
				'MarkFormFields' => [ 'sort' => [ 'MarkFormFields.priority' => 'asc' ] ],
				'MarkFormFields.MarkFormProperties'
			],
		] );

		$this->set( 'markForm', $markForm );
		$this->set( '_serialize', [ 'markForm' ] );
	}

	/**
	 * Add method
	 *
	 * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$markForm = $this->MarkForms->newEntity();
		if ( $this->request->is( 'post' ) ) {
			// make form fields savable
			$this->_prepareFormFields();
			$markForm = $this->MarkForms->patchEntity( $markForm, $this->request->getData());
			if ( $this->MarkForms->save( $markForm ) ) {
				$this->Flash->success( __( 'The mark form has been saved.' ) );

				return $this->redirect( [ 'action' => 'index' ] );
			} else {
				$this->Flash->error( __( 'The mark form could not be saved. Please, try again.' ) );
			}
		}
		$markFormProperties = $this->MarkForms->MarkFormFields->MarkFormProperties->find( 'list' );
		$this->set( compact( 'markForm', 'markFormProperties' ) );
		$this->set( '_serialize', [ 'markForm', 'markFormProperties' ] );
	}

	/**
	 * Make the request data ready to be persited: put the form fields in the right form and add the priority
	 *
	 * @param  int|null $id of the form to update or null if it's an insert
	 */
	protected function _prepareFormFields( $id = null ) {
		if ( ! empty( $this->request->data['mark_form_fields']['mark_form_properties'] ) ) {
			$fields = array_keys( $this->request->data['mark_form_fields']['mark_form_properties'] );
			unset( $this->request->data['mark_form_fields'] );

			$i = 0;
			foreach ( $fields as $field ) {
				$this->request->data['mark_form_fields'][ $i ] = [
					'mark_form_property_id' => $field,
					'priority'              => $i,
				];

				/**
				 * make it updateable (by setting the id)
				 */
				if ( $id ) {
					$query = $this->MarkForms->MarkFormFields->find()
					                                         ->select( 'id' )
					                                         ->where( [ 'mark_form_id' => $id ] )
					                                         ->andWhere( [ 'mark_form_property_id' => $field ] )
					                                         ->first();

					if ( ! empty( $query->id ) ) {
						$this->request->data['mark_form_fields'][ $i ]['id'] = $query->id;
					}
				}
				$i ++;
			}
		}
	}

	/**
	 * Edit method
	 *
	 * @param string|null $id Mark Form id.
	 *
	 * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Network\Exception\NotFoundException When record not found.
	 */
	public function edit( $id = null ) {
		$markForm = $this->MarkForms->get( $id, [
			'contain' => [
				'MarkFormFields' => [ 'sort' => [ 'MarkFormFields.priority' => 'asc' ] ],
				'MarkFormFields.MarkFormProperties'
			],
		] );
		if ( $this->request->is( [ 'patch', 'post', 'put' ] ) ) {
			// make form fields savable
			$this->_prepareFormFields( $id );
			$markForm = $this->MarkForms->patchEntity( $markForm, $this->request->getData());
			if ( $this->MarkForms->save( $markForm ) ) {
				$this->_deleteDeletedFields( $markForm->id );
				$this->Flash->success( __( 'The mark form has been saved.' ) );

				return $this->redirect( [ 'action' => 'index' ] );
			} else {
				$this->Flash->error( __( 'The mark form could not be saved. Please, try again.' ) );
			}
		}
		$markFormProperties = $this->MarkForms->MarkFormFields->MarkFormProperties->find( 'list' );
		$this->set( compact( 'markForm', 'markFormProperties' ) );
		$this->set( '_serialize', [ 'markForm', 'markFormProperties' ] );
	}

	/**
	 * delete the fields that the user deleted
	 */
	protected function _deleteDeletedFields( $id ) {
		$fields = $this->MarkForms->MarkFormFields->find()
		                                          ->where( [ 'mark_form_id' => $id ] )
		                                          ->toArray();

		$mark_form_property_ids = [];

		foreach ( $fields as $field ) {
			$mark_form_property_ids[] = $field->mark_form_property_id;
		}

		foreach ( $this->request->data['mark_form_fields'] as $mark_form_field ) {
			$key = array_search( $mark_form_field['mark_form_property_id'], $mark_form_property_ids );
			unset( $mark_form_property_ids[ $key ] );
		}

		foreach ( $mark_form_property_ids as $mark_form_property_id ) {
			$fields = $this->MarkForms->MarkFormFields->find()
			                                          ->where( [ 'mark_form_id' => $id ] )
			                                          ->andWhere( [ 'mark_form_property_id' => $mark_form_property_id ] )
			                                          ->first();
			$this->MarkForms->MarkFormFields->delete( $fields );
		}
	}

	/**
	 * Delete method
	 *
	 * @param string|null $id Mark Form id.
	 *
	 * @return \Cake\Network\Response|null Redirects to index.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function delete( $id = null ) {
		$this->request->allowMethod( [ 'post', 'delete' ] );
		$markForm = $this->MarkForms->get( $id );
		if ( $this->MarkForms->delete( $markForm ) ) {
			$this->Flash->success( __( 'The mark form has been deleted.' ) );
		} else {
			$this->Flash->error( __( 'The mark form could not be deleted. Please, try again.' ) );
		}

		return $this->redirect( [ 'action' => 'index' ] );
	}

	/**
	 * Return filtered index table
	 */
	public function filter() {
		$allowed_fields = [ 'name' ];

		if ( $this->request->is( 'get' )
		     && $this->request->is( 'ajax' )
		     && ! empty( $this->request->getQuery('fields') )
		     && array_intersect( $allowed_fields, $this->request->getQuery('fields') )
		) {
			$entries = $this->MarkForms->filter( $this->request->getQuery('term') );

			if ( ! empty( $this->request->getQuery('sort') ) ) {
				$sort                    = $this->request->getQuery('sort');
				$direction               = empty( $this->request->getQuery('direction') ) ? 'asc' : $this->request->getQuery('direction');
				$this->paginate['order'] = [ $sort => $direction ];
			}
			if ( ! empty( $this->request->getQuery('page') ) ) {
				$this->paginate['page'] = $this->request->getQuery('page');
			}

		} else {
			throw new Exception( __( 'Direct access not allowed.' ) );
		}

		if ( $entries ) {
			$markForms = $this->paginate( $entries );
			$this->set( compact( 'markForms' ) );
			$this->set( '_serialize', [ 'markForms' ] );
			$this->render( '/Element/MarkForm/index_table' );
		} else {
			$this->render( '/Element/nothing_found' );
		}
	}
}
