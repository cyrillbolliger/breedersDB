<?php

namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Exception\Exception;

/**
 * Crossings Controller
 *
 * @property \App\Model\Table\CrossingsTable $Crossings
 */
class CrossingsController extends AppController {
	public $paginate = [
		'order' => [ 'modified' => 'desc' ],
		'limit' => 100,
	];

    public function initialize(): void {
        parent::initialize();
        $this->loadComponent( 'Filter' );
    }

	/**
	 * Index method
	 *
	 * @return \Cake\Network\Response|null
	 */
	public function index() {
		$this->paginate['contain'] = [ 'MotherTrees' ];
		$crossings                 = $this->paginate( $this->Crossings->find( 'withoutOfficialVarieties'));

		$this->set( compact( 'crossings' ) );
		$this->set( '_serialize', [ 'crossings' ] );
	}

	/**
	 * View method
	 *
	 * @param string|null $id Crossing id.
	 *
	 * @return \Cake\Network\Response|null
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function view( $id = null ) {
		$crossing = $this->Crossings->get( $id, [
			'contain' => [ 'MotherTrees', 'Batches', 'MotherTrees.Trees' ]
		] );

		$mother_variety = null;
		if ( $crossing->mother_variety_id ) {
			$mother_variety = $this->Crossings->Varieties->get( $crossing->mother_variety_id, [
				'contain' => [ 'Batches' ]
			] );
		}

		$father_variety = null;
		if ( $crossing->father_variety_id ) {
			$father_variety = $this->Crossings->Varieties->get( $crossing->father_variety_id, [
				'contain' => [ 'Batches' ]
			] );
		}

		$this->set( compact( 'crossing', 'mother_variety', 'father_variety' ) );
		$this->set( '_serialize', [ 'crossing' ] );
	}

	/**
	 * Add method
	 *
	 * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$crossing         = $this->Crossings->newEmptyEntity();
		$mother_varieties = array();
		$father_varieties = array();
		$trees            = array();

		if ( $this->request->is( 'post' ) ) {
			$crossing = $this->Crossings->patchEntity( $crossing, $this->request->getData());
			if ( $this->Crossings->save( $crossing ) ) {
				$this->Flash->success( __( 'The crossing has been saved.' ) );

				return $this->redirect( [ 'action' => 'index' ] );
			} else {
				$this->Flash->error( __( 'The crossing could not be saved. Please try again.' ) );

				if ( $crossing->mother_variety_id ) {
					$mother_varieties = $this->Crossings->Varieties->getConvarList( $crossing->mother_variety_id );
				}
				if ( $crossing->father_variety_id ) {
					$father_varieties = $this->Crossings->Varieties->getConvarList( $crossing->father_variety_id );
				}
				if ( $crossing->mother_tree_id ) {
					$trees = $this->Crossings->Trees->getIdPublicidAndConvarList( $crossing->mother_tree_id );
				}
			}
		}

		$this->set( compact( 'crossing', 'mother_varieties', 'father_varieties', 'trees' ) );
		$this->set( '_serialize', [ 'crossing' ] );
	}

	/**
	 * Edit method
	 *
	 * @param string|null $id Crossing id.
	 *
	 * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Network\Exception\NotFoundException When record not found.
	 */
	public function edit( $id = null ) {
		$crossing = $this->Crossings->get( $id, [
			'contain' => []
		] );

		$mother_varieties = array();
		$father_varieties = array();
		$trees            = array();

		if ( $this->request->is( [ 'patch', 'post', 'put' ] ) ) {
			$crossing = $this->Crossings->patchEntity( $crossing, $this->request->getData());
			if ( $this->Crossings->save( $crossing ) ) {
				$this->Flash->success( __( 'The crossing has been saved.' ) );

				return $this->redirect( [ 'action' => 'index' ] );
			} else {
				$this->Flash->error( __( 'The crossing could not be saved. Please, try again.' ) );
			}
		}

		if ( $crossing->mother_variety_id ) {
			$mother_varieties = $this->Crossings->Varieties->getConvarList( $crossing->mother_variety_id );
		}
		if ( $crossing->father_variety_id ) {
			$father_varieties = $this->Crossings->Varieties->getConvarList( $crossing->father_variety_id );
		}
		if ( $crossing->mother_tree_id ) {
			$trees = $this->Crossings->Trees->getIdPublicidAndConvarList( $crossing->mother_tree_id );
		}

		$this->set( compact( 'crossing', 'mother_varieties', 'father_varieties', 'trees' ) );
		$this->set( '_serialize', [ 'crossing' ] );
	}

	/**
	 * Delete method
	 *
	 * @param string|null $id Crossing id.
	 *
	 * @return \Cake\Network\Response|null Redirects to index.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function delete( $id = null ) {
		$this->request->allowMethod( [ 'post', 'delete' ] );
		$crossing = $this->Crossings->get( $id );
		if ( $this->Crossings->delete( $crossing ) ) {
			$this->Flash->success( __( 'The crossing has been deleted.' ) );
		} else {
			$this->Flash->error( __( 'The crossing could not be deleted. Please, try again.' ) );
		}

		return $this->redirect( [ 'action' => 'index' ] );
	}

	/**
	 * Return filtered index table
	 */
	public function filter() {
		$allowed_fields = [ 'code' ];

		if ( $this->request->is( 'get' )
		     && $this->request->is( 'ajax' )
		     && ! empty( $this->request->getQuery('fields') )
		     && array_intersect( $allowed_fields, $this->request->getQuery('fields') )
		) {
			$entries = $this->Crossings->filterCodes( $this->request->getQuery('term') )
                                       ->find('withoutOfficialVarieties');
		} else {
			throw new \Exception( __( 'Direct access not allowed.' ) );
		}

		if ( $entries && $entries->count() ) {
            $this->Filter->setSortingParams();
            $this->Filter->setPaginationParams($entries);
			$crossings = $this->paginate( $entries );

			$this->set( compact( 'crossings' ) );
			$this->set( '_serialize', [ 'crossings' ] );
			$this->render( '/element/Crossing/index_table' );
		} else {
			$this->render( '/element/nothing_found' );
		}
	}
}
