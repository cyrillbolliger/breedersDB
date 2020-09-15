<?php

namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Exception\Exception;

/**
 * ScionsBundles Controller
 *
 * @property \App\Model\Table\ScionsBundlesTable $ScionsBundles
 */
class ScionsBundlesController extends AppController {

	public $paginate = [
		'order' => [ 'modified' => 'desc' ],
		'limit' => 100,
	];

    public function initialize() {
        parent::initialize();
        $this->loadComponent( 'Filter' );
    }

	/**
	 * Index method
	 *
	 * @return \Cake\Network\Response|null
	 */
	public function index() {
		$this->paginate['contain'] = [
			'Varieties'
		];
		$scionsBundles             = $this->paginate( $this->ScionsBundles );

		$this->set( compact( 'scionsBundles' ) );
		$this->set( '_serialize', [ 'scionsBundles' ] );
	}

	/**
	 * View method
	 *
	 * @param string|null $id Scions Bundle id.
	 *
	 * @return \Cake\Network\Response|null
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function view( $id = null ) {
		$scionsBundle = $this->ScionsBundles->get( $id, [
			'contain' => [ 'Varieties' ]
		] );

		$this->set( 'scionsBundle', $scionsBundle );
		$this->set( '_serialize', [ 'scionsBundle' ] );
	}

	/**
	 * Add method
	 *
	 * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$scionsBundle = $this->ScionsBundles->newEntity();
		$varieties    = array();

		if ( $this->request->is( 'post' ) ) {
			$scionsBundle = $this->ScionsBundles->patchEntity( $scionsBundle, $this->request->getData());
			if ( $this->ScionsBundles->save( $scionsBundle ) ) {
				$this->Flash->success( __( 'The scions bundle has been saved.' ) );

				return $this->redirect( [ 'action' => 'index' ] );
			} else {
				$this->Flash->error( __( 'The scions bundle could not be saved. Please, try again.' ) );

				if ( $scionsBundle->variety_id ) {
					$varieties = $this->ScionsBundles->Varieties->getConvarList( $scionsBundle->variety_id );
				}
			}
		}
		$this->set( compact( 'scionsBundle', 'varieties' ) );
		$this->set( '_serialize', [ 'scionsBundle' ] );
	}

	/**
	 * Edit method
	 *
	 * @param string|null $id Scions Bundle id.
	 *
	 * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Network\Exception\NotFoundException When record not found.
	 */
	public function edit( $id = null ) {
		$scionsBundle = $this->ScionsBundles->get( $id, [
			'contain' => []
		] );

		if ( $this->request->is( [ 'patch', 'post', 'put' ] ) ) {
			$scionsBundle = $this->ScionsBundles->patchEntity( $scionsBundle, $this->request->getData());
			if ( $this->ScionsBundles->save( $scionsBundle ) ) {
				$this->Flash->success( __( 'The scions bundle has been saved.' ) );

				return $this->redirect( [ 'action' => 'index' ] );
			} else {
				$this->Flash->error( __( 'The scions bundle could not be saved. Please, try again.' ) );
			}
		}
		$varieties = $this->ScionsBundles->Varieties->getConvarList( $scionsBundle->variety_id );

		$this->set( compact( 'scionsBundle', 'varieties' ) );
		$this->set( '_serialize', [ 'scionsBundle' ] );
	}

	/**
	 * Delete method
	 *
	 * @param string|null $id Scions Bundle id.
	 *
	 * @return \Cake\Network\Response|null Redirects to index.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function delete( $id = null ) {
		$this->request->allowMethod( [ 'post', 'delete' ] );
		$scionsBundle = $this->ScionsBundles->get( $id );
		if ( $this->ScionsBundles->delete( $scionsBundle ) ) {
			$this->Flash->success( __( 'The scions bundle has been deleted.' ) );
		} else {
			$this->Flash->error( __( 'The scions bundle could not be deleted. Please, try again.' ) );
		}

		return $this->redirect( [ 'action' => 'index' ] );
	}

	/**
	 * Return filtered index table
	 */
	public function filter() {
		$allowed_fields = [ 'code', 'convar' ];

		if ( $this->request->is( 'get' )
		     && $this->request->is( 'ajax' )
		     && ! empty( $this->request->getQuery('fields') )
		     && array_intersect( $allowed_fields, $this->request->getQuery('fields') )
		) {
			$entries = $this->ScionsBundles->filter( $this->request->getQuery('term') );

            $this->Filter->setSortingParams();
            $this->Filter->setPaginationParams($entries);

		} else {
			throw new Exception( __( 'Direct access not allowed.' ) );
		}

		if ( $entries->count() ) {
			$scionsBundles = $this->paginate( $entries );
			$this->set( compact( 'scionsBundles' ) );
			$this->set( '_serialize', [ 'scionsBundles' ] );
			$this->render( '/Element/ScionsBundle/index_table' );
		} else {
			$this->render( '/Element/nothing_found' );
		}
	}
}
