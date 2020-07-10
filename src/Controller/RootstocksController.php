<?php

namespace App\Controller;

use App\Controller\AppController;

/**
 * Rootstocks Controller
 *
 * @property \App\Model\Table\RootstocksTable $Rootstocks
 */
class RootstocksController extends AppController {

	/**
	 * Index method
	 *
	 * @return \Cake\Network\Response|null
	 */
	public function index() {
		$rootstocks = $this->paginate( $this->Rootstocks );

		$this->set( compact( 'rootstocks' ) );
		$this->set( '_serialize', [ 'rootstocks' ] );
	}

	/**
	 * View method
	 *
	 * @param string|null $id Rootstock id.
	 *
	 * @return \Cake\Network\Response|null
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function view( $id = null ) {
		$rootstock = $this->Rootstocks->get( $id, [
			'contain' => [ 'Trees' ]
		] );

		$this->set( 'rootstock', $rootstock );
		$this->set( '_serialize', [ 'rootstock' ] );
	}

	/**
	 * Add method
	 *
	 * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$rootstock = $this->Rootstocks->newEntity();
		if ( $this->request->is( 'post' ) ) {
			$rootstock = $this->Rootstocks->patchEntity( $rootstock, $this->request->getData());
			if ( $this->Rootstocks->save( $rootstock ) ) {
				$this->Flash->success( __( 'The rootstock has been saved.' ) );

				return $this->redirect( [ 'action' => 'index' ] );
			} else {
				$this->Flash->error( __( 'The rootstock could not be saved. Please, try again.' ) );
			}
		}
		$this->set( compact( 'rootstock' ) );
		$this->set( '_serialize', [ 'rootstock' ] );
	}

	/**
	 * Edit method
	 *
	 * @param string|null $id Rootstock id.
	 *
	 * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Network\Exception\NotFoundException When record not found.
	 */
	public function edit( $id = null ) {
		$rootstock = $this->Rootstocks->get( $id, [
			'contain' => []
		] );
		if ( $this->request->is( [ 'patch', 'post', 'put' ] ) ) {
			$rootstock = $this->Rootstocks->patchEntity( $rootstock, $this->request->getData());
			if ( $this->Rootstocks->save( $rootstock ) ) {
				$this->Flash->success( __( 'The rootstock has been saved.' ) );

				return $this->redirect( [ 'action' => 'index' ] );
			} else {
				$this->Flash->error( __( 'The rootstock could not be saved. Please, try again.' ) );
			}
		}
		$this->set( compact( 'rootstock' ) );
		$this->set( '_serialize', [ 'rootstock' ] );
	}

	/**
	 * Delete method
	 *
	 * @param string|null $id Rootstock id.
	 *
	 * @return \Cake\Network\Response|null Redirects to index.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function delete( $id = null ) {
		$this->request->allowMethod( [ 'post', 'delete' ] );
		$rootstock = $this->Rootstocks->get( $id );
		if ( $this->Rootstocks->delete( $rootstock ) ) {
			$this->Flash->success( __( 'The rootstock has been deleted.' ) );
		} else {
			$this->Flash->error( __( 'The rootstock could not be deleted. Please, try again.' ) );
		}

		return $this->redirect( [ 'action' => 'index' ] );
	}
}
