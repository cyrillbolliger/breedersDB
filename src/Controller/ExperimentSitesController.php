<?php

namespace App\Controller;

use App\Controller\AppController;

/**
 * ExperimentSites Controller
 *
 * @property \App\Model\Table\ExperimentSitesTable $ExperimentSites
 */
class ExperimentSitesController extends AppController {

	/**
	 * Index method
	 *
	 * @return \Cake\Network\Response|null
	 */
	public function index() {
		$experimentSites = $this->paginate( $this->ExperimentSites );

		$this->set( compact( 'experimentSites' ) );
		$this->set( '_serialize', [ 'experimentSites' ] );
	}

	/**
	 * View method
	 *
	 * @param string|null $id Experiment Site id.
	 *
	 * @return \Cake\Network\Response|null
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function view( $id = null ) {
		$experimentSite = $this->ExperimentSites->get( $id );

		$this->set( 'experimentSite', $experimentSite );
		$this->set( '_serialize', [ 'experimentSite' ] );
	}

	/**
	 * Add method
	 *
	 * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$experimentSite = $this->ExperimentSites->newEmptyEntity();
		if ( $this->request->is( 'post' ) ) {
			$experimentSite = $this->ExperimentSites->patchEntity( $experimentSite, $this->request->getData());
			if ( $this->ExperimentSites->save( $experimentSite ) ) {
				$this->Flash->success( __( 'The experiment site has been saved.' ) );

				return $this->redirect( [ 'action' => 'index' ] );
			} else {
				$this->Flash->error( __( 'The experiment site could not be saved. Please, try again.' ) );
			}
		}
		$this->set( compact( 'experimentSite' ) );
		$this->set( '_serialize', [ 'experimentSite' ] );
	}

	/**
	 * Edit method
	 *
	 * @param string|null $id Experiment Site id.
	 *
	 * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Network\Exception\NotFoundException When record not found.
	 */
	public function edit( $id = null ) {
		$experimentSite = $this->ExperimentSites->get( $id, [
			'contain' => []
		] );
		if ( $this->request->is( [ 'patch', 'post', 'put' ] ) ) {
			$experimentSite = $this->ExperimentSites->patchEntity( $experimentSite, $this->request->getData());
			if ( $this->ExperimentSites->save( $experimentSite ) ) {
				$this->Flash->success( __( 'The experiment site has been saved.' ) );

				return $this->redirect( [ 'action' => 'index' ] );
			} else {
				$this->Flash->error( __( 'The experiment site could not be saved. Please, try again.' ) );
			}
		}
		$this->set( compact( 'experimentSite' ) );
		$this->set( '_serialize', [ 'experimentSite' ] );
	}

	/**
	 * Delete method
	 *
	 * @param string|null $id Experiment Site id.
	 *
	 * @return \Cake\Network\Response|null Redirects to index.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function delete( $id = null ) {
		$this->request->allowMethod( [ 'post', 'delete' ] );
		$experimentSite = $this->ExperimentSites->get( $id );
		if ( $this->ExperimentSites->delete( $experimentSite ) ) {
			$this->Flash->success( __( 'The experiment site has been deleted.' ) );
		} else {
			$this->Flash->error( __( 'The experiment site could not be deleted. Please, try again.' ) );
		}

		return $this->redirect( [ 'action' => 'index' ] );
	}

	/**
	 * Select method
	 */
	public function select() {
		// if site was selected
		if ( $this->request->is( 'post' ) && ! empty( $this->request->getData('experiment_site_id') ) ) {

			// get site data
			$id             = (int) $this->request->getData('experiment_site_id');
			$experimentSite = $this->ExperimentSites->get( $id );

			// add the site to the session
			$session = $this->request->getSession();
			$session->write( 'experiment_site_id', (int) $id );
			$session->write( 'experiment_site_name', $experimentSite->name );

			// and redirect the user
			if ( $session->read( 'redirect_after_action' ) ) {
				$redirect = $this->redirect( $session->read( 'redirect_after_action' ) );
				$session->delete( 'redirect_after_action' );
			} else {
				$redirect = $this->redirect( [ 'controller' => 'Trees', 'action' => 'index' ] );
			}

			return $redirect;
		}

		// show the selection form
		$experimentSites = $this->ExperimentSites->find( 'list' );
		$this->set( compact( 'experimentSites' ) );
		$this->set( '_serialize', [ 'experimentSites' ] );
	}
}
