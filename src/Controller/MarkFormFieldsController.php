<?php

namespace App\Controller;

use App\Controller\AppController;

/**
 * MarkFormFields Controller
 *
 * @property \App\Model\Table\MarkFormFieldsTable $MarkFormFields
 */
class MarkFormFieldsController extends AppController {

	/**
	 * Index method
	 *
	 * @return \Cake\Network\Response|null
	 */
	public function index() {
		$this->paginate = [
			'contain' => [ 'MarkForms', 'MarkFormProperties' ]
		];
		$markFormFields = $this->paginate( $this->MarkFormFields );

		$this->set( compact( 'markFormFields' ) );
		$this->set( '_serialize', [ 'markFormFields' ] );
	}

	/**
	 * View method
	 *
	 * @param string|null $id Mark Form Field id.
	 *
	 * @return \Cake\Network\Response|null
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function view( $id = null ) {
		$markFormField = $this->MarkFormFields->get( $id, [
			'contain' => [ 'MarkForms', 'MarkFormProperties' ]
		] );

		$this->set( 'markFormField', $markFormField );
		$this->set( '_serialize', [ 'markFormField' ] );
	}

	/**
	 * Add method
	 *
	 * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$markFormField = $this->MarkFormFields->newEmptyEntity();
		if ( $this->request->is( 'post' ) ) {
			$markFormField = $this->MarkFormFields->patchEntity( $markFormField, $this->request->getData());
			if ( $this->MarkFormFields->save( $markFormField ) ) {
				$this->Flash->success( __( 'The mark form field has been saved.' ) );

				return $this->redirect( [ 'action' => 'index' ] );
			} else {
				$this->Flash->error( __( 'The mark form field could not be saved. Please, try again.' ) );
			}
		}
		$markForms          = $this->MarkFormFields->MarkForms->find( 'list' );
		$markFormProperties = $this->MarkFormFields->MarkFormProperties->find( 'list' );
		$this->set( compact( 'markFormField', 'markForms', 'markFormProperties' ) );
		$this->set( '_serialize', [ 'markFormField' ] );
	}

	/**
	 * Edit method
	 *
	 * @param string|null $id Mark Form Field id.
	 *
	 * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Network\Exception\NotFoundException When record not found.
	 */
	public function edit( $id = null ) {
		$markFormField = $this->MarkFormFields->get( $id, [
			'contain' => []
		] );
		if ( $this->request->is( [ 'patch', 'post', 'put' ] ) ) {
			$markFormField = $this->MarkFormFields->patchEntity( $markFormField, $this->request->getData());
			if ( $this->MarkFormFields->save( $markFormField ) ) {
				$this->Flash->success( __( 'The mark form field has been saved.' ) );

				return $this->redirect( [ 'action' => 'index' ] );
			} else {
				$this->Flash->error( __( 'The mark form field could not be saved. Please, try again.' ) );
			}
		}
		$markForms          = $this->MarkFormFields->MarkForms->find( 'list' );
		$markFormProperties = $this->MarkFormFields->MarkFormProperties->find( 'list' );
		$this->set( compact( 'markFormField', 'markForms', 'markFormProperties' ) );
		$this->set( '_serialize', [ 'markFormField' ] );
	}

	/**
	 * Delete method
	 *
	 * @param string|null $id Mark Form Field id.
	 *
	 * @return \Cake\Network\Response|null Redirects to index.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function delete( $id = null ) {
		$this->request->allowMethod( [ 'post', 'delete' ] );
		$markFormField = $this->MarkFormFields->get( $id );
		if ( $this->MarkFormFields->delete( $markFormField ) ) {
			$this->Flash->success( __( 'The mark form field has been deleted.' ) );
		} else {
			$this->Flash->error( __( 'The mark form field could not be deleted. Please, try again.' ) );
		}

		return $this->redirect( [ 'action' => 'index' ] );
	}
}
