<?php

namespace App\Controller;

use App\Controller\AppController;

/**
 * MarkFormPropertyTypes Controller
 *
 * @property \App\Model\Table\MarkFormPropertyTypesTable $MarkFormPropertyTypes
 */
class MarkFormPropertyTypesController extends AppController {

	/**
	 * Index method
	 *
	 * @return \Cake\Network\Response|null
	 */
	public function index() {
		$markFormPropertyTypes = $this->paginate( $this->MarkFormPropertyTypes );

		$this->set( compact( 'markFormPropertyTypes' ) );
		$this->set( '_serialize', [ 'markFormPropertyTypes' ] );
	}

	/**
	 * View method
	 *
	 * @param string|null $id Mark Form Property Type id.
	 *
	 * @return \Cake\Network\Response|null
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function view( $id = null ) {
		$markFormPropertyType = $this->MarkFormPropertyTypes->get( $id, [
			'contain' => [ 'MarkFormProperties' ]
		] );

		$this->set( 'markFormPropertyType', $markFormPropertyType );
		$this->set( '_serialize', [ 'markFormPropertyType' ] );
	}

	/**
	 * Add method
	 *
	 * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$markFormPropertyType = $this->MarkFormPropertyTypes->newEmptyEntity();
		if ( $this->request->is( 'post' ) ) {
			$markFormPropertyType = $this->MarkFormPropertyTypes->patchEntity( $markFormPropertyType,
				$this->request->getData());
			if ( $this->MarkFormPropertyTypes->save( $markFormPropertyType ) ) {
				$this->Flash->success( __( 'The mark form property type has been saved.' ) );

				return $this->redirect( [ 'action' => 'index' ] );
			} else {
				$this->Flash->error( __( 'The mark form property type could not be saved. Please, try again.' ) );
			}
		}
		$this->set( compact( 'markFormPropertyType' ) );
		$this->set( '_serialize', [ 'markFormPropertyType' ] );
	}

	/**
	 * Edit method
	 *
	 * @param string|null $id Mark Form Property Type id.
	 *
	 * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Network\Exception\NotFoundException When record not found.
	 */
	public function edit( $id = null ) {
		$markFormPropertyType = $this->MarkFormPropertyTypes->get( $id, [
			'contain' => []
		] );
		if ( $this->request->is( [ 'patch', 'post', 'put' ] ) ) {
			$markFormPropertyType = $this->MarkFormPropertyTypes->patchEntity( $markFormPropertyType,
				$this->request->getData());
			if ( $this->MarkFormPropertyTypes->save( $markFormPropertyType ) ) {
				$this->Flash->success( __( 'The mark form property type has been saved.' ) );

				return $this->redirect( [ 'action' => 'index' ] );
			} else {
				$this->Flash->error( __( 'The mark form property type could not be saved. Please, try again.' ) );
			}
		}
		$this->set( compact( 'markFormPropertyType' ) );
		$this->set( '_serialize', [ 'markFormPropertyType' ] );
	}

	/**
	 * Delete method
	 *
	 * @param string|null $id Mark Form Property Type id.
	 *
	 * @return \Cake\Network\Response|null Redirects to index.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function delete( $id = null ) {
		$this->request->allowMethod( [ 'post', 'delete' ] );
		$markFormPropertyType = $this->MarkFormPropertyTypes->get( $id );
		if ( $this->MarkFormPropertyTypes->delete( $markFormPropertyType ) ) {
			$this->Flash->success( __( 'The mark form property type has been deleted.' ) );
		} else {
			$this->Flash->error( __( 'The mark form property type could not be deleted. Please, try again.' ) );
		}

		return $this->redirect( [ 'action' => 'index' ] );
	}
}
