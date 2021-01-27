<?php

namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Exception\Exception;

/**
 * MotherTrees Controller
 *
 * @property \App\Model\Table\MotherTreesTable $MotherTrees
 */
class MotherTreesController extends AppController {
	public $paginate = [
		'order' => [ 'modified' => 'desc' ],
		'limit' => 100,
	];

	public function initialize(): void {
		parent::initialize();
		$this->loadComponent( 'Brain' );
        $this->loadComponent( 'Filter' );
	}

	/**
	 * Index method
	 *
	 * @return \Cake\Network\Response|null
	 */
	public function index() {
		$this->paginate['contain'] = [ 'Crossings', 'Trees' ];

		$motherTrees = $this->paginate( $this->MotherTrees->find('withEliminated', ['show_eliminated' => false]) );

		$this->set( compact( 'motherTrees' ) );
		$this->set( '_serialize', [ 'motherTrees' ] );
	}

	/**
	 * View method
	 *
	 * @param string|null $id Mother Tree id.
	 *
	 * @return \Cake\Network\Response|null
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function view( $id = null ) {
		$motherTree = $this->MotherTrees->get( $id, [
			'contain' => [ 'Crossings', 'Trees' ]
		] );

		$this->set( 'motherTree', $motherTree );
		$this->set( '_serialize', [ 'motherTree' ] );
	}

	/**
	 * Add method
	 *
	 * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$motherTree = $this->MotherTrees->newEntity();
		if ( $this->request->is( 'post' ) ) {
			$motherTree = $this->MotherTrees->patchEntity( $motherTree, $this->request->getData());
			if ( $this->MotherTrees->save( $motherTree ) ) {
				$this->Flash->success( __( 'The mother tree has been saved.' ) );

				return $this->redirect( [ 'action' => 'index' ] );
			} else {
				$this->Flash->error( __( 'The mother tree could not be saved. Please, try again.' ) );
			}
		}
		$motherTree = $this->Brain->remember( $motherTree );
		$crossings  = $this->MotherTrees->Crossings->find( 'list' );
		$trees      = $this->MotherTrees->Trees->find( 'list' );
		$this->set( compact( 'motherTree', 'crossings', 'trees' ) );
		$this->set( '_serialize', [ 'motherTree' ] );
	}

	/**
	 * Edit method
	 *
	 * @param string|null $id Mother Tree id.
	 *
	 * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Network\Exception\NotFoundException When record not found.
	 */
	public function edit( $id = null ) {
		$motherTree = $this->MotherTrees->get( $id, [
			'contain' => [
				'Trees',
				'Trees.Rows',
				'Trees.ExperimentSites',
				'Trees.Varieties',
			]
		] );
		if ( $this->request->is( [ 'patch', 'post', 'put' ] ) ) {
			$motherTree = $this->MotherTrees->patchEntity( $motherTree, $this->request->getData());
			if ( $this->MotherTrees->save( $motherTree ) ) {
				$this->Flash->success( __( 'The mother tree has been saved.' ) );

				return $this->redirect( [ 'action' => 'index' ] );
			} else {
				$this->Flash->error( __( 'The mother tree could not be saved. Please, try again.' ) );
			}
		}
		$motherTree = $this->Brain->remember( $motherTree );
		$crossings  = $this->MotherTrees->Crossings->find( 'list' );
		$trees      = $this->MotherTrees->Trees->find( 'list' );
		$this->set( compact( 'motherTree', 'crossings', 'trees' ) );
		$this->set( '_serialize', [ 'motherTree' ] );
	}

	/**
	 * Delete method
	 *
	 * @param string|null $id Mother Tree id.
	 *
	 * @return \Cake\Network\Response|null Redirects to index.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function delete( $id = null ) {
		$this->request->allowMethod( [ 'post', 'delete' ] );
		$motherTree = $this->MotherTrees->get( $id );
		if ( $this->MotherTrees->delete( $motherTree ) ) {
			$this->Flash->success( __( 'The mother tree has been deleted.' ) );
		} else {
			$this->Flash->error( __( 'The mother tree could not be deleted. Please, try again.' ) );
		}

		return $this->redirect( [ 'action' => 'index' ] );
	}

	/**
	 * Return filtered index table
	 */
	public function filter() {
		$allowed_fields = [ 'code', 'publicid' ];

		if ( $this->request->is( 'get' )
		     && $this->request->is( 'ajax' )
		     && ! empty( $this->request->getQuery('fields') )
		     && array_intersect( $allowed_fields, $this->request->getQuery('fields') )
		) {
            $term = $this->request->getQuery('term');
            $entries = $this->MotherTrees->filterCodes( $term );

            if ( $entries && $entries->count() ) {
                $show_eliminated = filter_var( $this->request->getQuery('options.show_eliminated', false), FILTER_VALIDATE_BOOL);
                $entries = $entries->find('withEliminated', ['show_eliminated' => $show_eliminated]);
            }
		} else {
			throw new Exception( __( 'Direct access not allowed.' ) );
		}

		if ( $entries && $entries->count() ) {
            $this->Filter->setSortingParams();
            $this->Filter->setPaginationParams($entries);
            $motherTrees = $this->paginate( $entries );

			$this->set( compact( 'motherTrees' ) );
			$this->set( '_serialize', [ 'motherTrees' ] );
			$this->render( '/Element/MotherTree/index_table' );
		} else {
			$this->render( '/Element/nothing_found' );
		}
	}
}
