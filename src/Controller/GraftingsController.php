<?php

namespace App\Controller;

use App\Controller\AppController;

/**
 * Graftings Controller
 *
 * @property \App\Model\Table\GraftingsTable $Graftings
 */
class GraftingsController extends AppController {

	/**
	 * Index method
	 *
	 * @return \Cake\Network\Response|null
	 */
	public function index() {
		$graftings = $this->paginate( $this->Graftings );

		$this->set( compact( 'graftings' ) );
		$this->set( '_serialize', [ 'graftings' ] );
	}

	/**
	 * View method
	 *
	 * @param string|null $id Grafting id.
	 *
	 * @return \Cake\Network\Response|null
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function view( $id = null ) {
		$grafting = $this->Graftings->get( $id, [
			'contain' => [ 'Trees' ]
		] );

		$this->set( 'grafting', $grafting );
		$this->set( '_serialize', [ 'grafting' ] );
	}

	/**
	 * Add method
	 *
	 * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$grafting = $this->Graftings->newEntity();
		if ( $this->request->is( 'post' ) ) {
			$grafting = $this->Graftings->patchEntity( $grafting, $this->request->getData());
			if ( $this->Graftings->save( $grafting ) ) {
				$this->Flash->success( __( 'The grafting has been saved.' ) );

				return $this->redirect( [ 'action' => 'index' ] );
			} else {
				$this->Flash->error( __( 'The grafting could not be saved. Please, try again.' ) );
			}
		}
		$this->set( compact( 'grafting' ) );
		$this->set( '_serialize', [ 'grafting' ] );
	}

	/**
	 * Edit method
	 *
	 * @param string|null $id Grafting id.
	 *
	 * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Network\Exception\NotFoundException When record not found.
	 */
	public function edit( $id = null ) {
		$grafting = $this->Graftings->get( $id, [
			'contain' => []
		] );
		if ( $this->request->is( [ 'patch', 'post', 'put' ] ) ) {
			$grafting = $this->Graftings->patchEntity( $grafting, $this->request->getData());
			if ( $this->Graftings->save( $grafting ) ) {
				$this->Flash->success( __( 'The grafting has been saved.' ) );

				return $this->redirect( [ 'action' => 'index' ] );
			} else {
				$this->Flash->error( __( 'The grafting could not be saved. Please, try again.' ) );
			}
		}
		$this->set( compact( 'grafting' ) );
		$this->set( '_serialize', [ 'grafting' ] );
	}

	/**
	 * Delete method
	 *
	 * @param string|null $id Grafting id.
	 *
	 * @return \Cake\Network\Response|null Redirects to index.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function delete( $id = null ) {
		$this->request->allowMethod( [ 'post', 'delete' ] );
		$grafting = $this->Graftings->get( $id );
		if ( $this->Graftings->delete( $grafting ) ) {
			$this->Flash->success( __( 'The grafting has been deleted.' ) );
		} else {
			$this->Flash->error( __( 'The grafting could not be deleted. Please, try again.' ) );
		}

		return $this->redirect( [ 'action' => 'index' ] );
	}
}
