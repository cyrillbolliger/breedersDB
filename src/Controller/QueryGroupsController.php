<?php

namespace App\Controller;

use App\Controller\AppController;

/**
 * QueryGroups Controller
 *
 * @property \App\Model\Table\QueryGroupsTable $QueryGroups
 */
class QueryGroupsController extends AppController {

	/**
	 * Add method
	 *
	 * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$queryGroup = $this->QueryGroups->newEntity();
		if ( $this->request->is( 'post' ) ) {
			$queryGroup = $this->QueryGroups->patchEntity( $queryGroup, $this->request->getData());
			if ( $this->QueryGroups->save( $queryGroup ) ) {
				$this->Flash->success( __( 'The query group has been saved.' ) );

				return $this->redirect( [ 'controller' => 'Queries', 'action' => 'index' ] );
			} else {
				$this->Flash->error( __( 'The query group could not be saved. Please, try again.' ) );
			}
		}
		$queryGroups = $this->QueryGroups->find( 'all' )->contain( 'Queries' )->order( 'code' );
		$this->set( compact( 'queryGroup', 'queryGroups' ) );
		$this->set( '_serialize', [ 'queryGroup', 'queryGroups' ] );
	}

	/**
	 * Edit method
	 *
	 * @param string|null $id Query Group id.
	 *
	 * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Network\Exception\NotFoundException When record not found.
	 */
	public function edit( $id = null ) {
		$queryGroup = $this->QueryGroups->get( $id, [
			'contain' => []
		] );
		if ( $this->request->is( [ 'patch', 'post', 'put' ] ) ) {
			$queryGroup = $this->QueryGroups->patchEntity( $queryGroup, $this->request->getData());
			if ( $this->QueryGroups->save( $queryGroup ) ) {
				$this->Flash->success( __( 'The query group has been saved.' ) );

				return $this->redirect( [ 'controller' => 'Queries', 'action' => 'index' ] );
			} else {
				$this->Flash->error( __( 'The query group could not be saved. Please, try again.' ) );
			}
		}
		$queryGroups = $this->QueryGroups->find( 'all' )->contain( 'Queries' )->order( 'code' );
		$this->set( compact( 'queryGroup', 'queryGroups' ) );
		$this->set( '_serialize', [ 'queryGroup' ] );
	}

	/**
	 * Delete method
	 *
	 * @param string|null $id Query Group id.
	 *
	 * @return \Cake\Network\Response|null Redirects to index.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function delete( $id = null ) {
		$this->request->allowMethod( [ 'post', 'delete' ] );
		$queryGroup = $this->QueryGroups->get( $id );
		if ( $this->QueryGroups->delete( $queryGroup ) ) {
			$this->Flash->success( __( 'The query group has been deleted.' ) );
		} else {
			$this->Flash->error( __( 'The query group could not be deleted. Please, try again.' ) );
		}

		return $this->redirect( [ 'controller' => 'Queries', 'action' => 'index' ] );
	}
}
