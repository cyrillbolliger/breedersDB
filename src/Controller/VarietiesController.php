<?php

namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Exception\Exception;
use \Cake\Event\Event;

/**
 * Varieties Controller
 *
 * @property \App\Model\Table\VarietiesTable $Varieties
 */
class VarietiesController extends AppController {

	public $paginate = [
		'order' => [ 'modified' => 'desc' ],
		'limit' => 100,
	];

	public function initialize(): void {
		parent::initialize();
		$this->loadComponent( 'MarksReader' );
        $this->loadComponent( 'Filter' );
	}

	public function beforeFilter( \Cake\Event\EventInterface $event ) {
		parent::beforeFilter( $event );

		$this->FormProtection->setConfig( 'unlockedFields', [ 'code', 'batch_id' ] );
	}

	/**
	 * Index method
	 *
	 * @return \Cake\Network\Response|null
	 */
	public function index() {
		$this->paginate['sortableFields'] = [
			'convar',
			'official_name',
            'acronym',
			'created',
			'modified',
			'id',
		];

		$this->paginate['fields'] = [
			'id',
			'convar',
			'official_name',
            'acronym',
			'created',
			'modified',
		];

		$varieties = $this->paginate( $this->Varieties );

		$this->set( compact( 'varieties' ) );
		$this->set( '_serialize', [ 'varieties' ] );
	}

	/**
	 * View method
	 *
	 * @param string|null $id Variety id.
	 *
	 * @return \Cake\Network\Response|null
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function view( $id = null ) {
		$variety = $this->Varieties->get( $id, [
			'contain' => [ 'Batches', 'ScionsBundles', 'Trees', 'Marks' ]
		] );

		$tree_ids = array_map( function ( $tree ) {
			return $tree->id;
		}, $variety->trees );

		$marks = $this->MarksReader->get( $tree_ids, $id );
		$this->set( 'variety', $variety );
		$this->set( 'marks', $marks );
		$this->set( '_serialize', [ 'variety' ] );
	}

	/**
	 * Add method
	 *
	 * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
	 */
	public function addBreederVariety() {
		$variety = $this->Varieties->newEmptyEntity();
		$batches = array();
		if ( $this->request->is( 'post' ) ) {
			$variety = $this->Varieties->patchEntity( $variety, $this->request->getData());
			if ( $this->Varieties->save( $variety ) ) {
				$this->Flash->success( __( 'The variety has been saved.' ) );

				return $this->redirect( [ 'action' => 'index' ] );
			} else {
				$this->Flash->error( __( 'The variety could not be saved. Please, try again.' ) );

				$batches = $this->Varieties->Batches->getCrossingBatchList( $variety->batch_id );
			}
		}

		$disabled = empty( $batches ) ? 'disabled' : '';

		$this->set( compact( 'variety', 'batches', 'disabled' ) );
		$this->set( '_serialize', [ 'variety' ] );
	}

	/**
	 * Add method
	 *
	 * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
	 */
	public function addOfficialVariety() {
		$variety = $this->Varieties->newEmptyEntity();
		if ( $this->request->is( 'post' ) ) {
			$variety = $this->Varieties->patchEntity( $variety, $this->request->getData());
			if ( $this->Varieties->save( $variety ) ) {
				$this->Flash->success( __( 'The variety has been saved.' ) );

				return $this->redirect( [ 'action' => 'index' ] );
			} else {
				$this->Flash->error( __( 'The variety could not be saved. Please, try again.' ) );
			}
		}

		$this->set( compact( 'variety' ) );
		$this->set( '_serialize', [ 'variety' ] );
	}

	/**
	 * Edit method
	 *
	 * @param string|null $id Variety id.
	 *
	 * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Network\Exception\NotFoundException When record not found.
	 */
	public function edit( $id = null ) {
		$variety = $this->Varieties->get( $id, [
			'contain' => []
		] );
		if ( $this->request->is( [ 'patch', 'post', 'put' ] ) ) {
			$variety = $this->Varieties->patchEntity( $variety, $this->request->getData());
			if ( $this->Varieties->save( $variety ) ) {
				$this->Flash->success( __( 'The variety has been saved.' ) );

				return $this->redirect( [ 'action' => 'index' ] );
			} else {
				$this->Flash->error( __( 'The variety could not be saved. Please, try again.' ) );
			}
		}
		$batch = $this->Varieties->Batches->get( $variety->batch_id, [
			'contain' => [ 'Crossings' ],
			'fields'  => [ 'id', 'Crossings.code', 'Batches.code' ]
		] );

		$batches = [
			[
				$batch->id => $batch->crossing->code . '.' . $batch->code,
			],
		];

		$this->set( compact( 'variety', 'batches' ) );
		$this->set( '_serialize', [ 'variety' ] );
	}

	/**
	 * Delete method
	 *
	 * @param string|null $id Variety id.
	 *
	 * @return \Cake\Network\Response|null Redirects to index.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function delete( $id = null ) {
		$this->request->allowMethod( [ 'post', 'delete' ] );
		$variety = $this->Varieties->get( $id );
		if ( $this->Varieties->delete( $variety ) ) {
			$this->Flash->success( __( 'The variety has been deleted.' ) );
		} else {
			$this->Flash->error( __( 'The variety could not be deleted. Please, try again.' ) );
		}

		return $this->redirect( [ 'action' => 'index' ] );
	}

	/**
	 * Return list with crossing.batch as value and batch_id as key.
	 * Must be called as ajax get request.
	 */
	public function searchCrossingBatchs() {

		if ( $this->request->is( 'get' )
		     && $this->request->is( 'ajax' )
		     && ! empty( $this->request->getQuery('term') )
		) {
			$return = $this->Varieties->Batches->searchCrossingBatchs( $this->request->getQuery('term') );
		} else {
			throw new \Exception( __( 'Direct access not allowed.' ) );
		}

		$this->set( [ 'data' => $return ] );
		$this->render( '/element/ajaxreturn', 'raw' );
	}

	/**
	 * Return list with convar as value and id as key.
	 * Must be called as ajax get request.
	 */
	public function searchConvars() {

		if ( $this->request->is( 'get' )
		     && $this->request->is( 'ajax' )
		     && ! empty( $this->request->getQuery('term') )
		) {
			$return = $this->Varieties->searchConvars( $this->request->getQuery('term') );
		} else {
			throw new \Exception( __( 'Direct access not allowed.' ) );
		}

		$this->set( [ 'data' => $return ] );
		$this->render( '/element/ajaxreturn', 'raw');
	}

	/**
	 * Return next free code respectiong given batch_id.
	 * Must be called as ajax get request.
	 */
	public function getNextFreeCode() {

		if ( $this->request->is( 'get' )
		     && $this->request->is( 'ajax' )
		     && ! empty( $this->request->getQuery('batch_id') )
		) {
			$return = $this->Varieties->getNextFreeCode( (int) $this->request->getQuery('batch_id') );
		} else {
			throw new \Exception( __( 'Direct access not allowed.' ) );
		}

		$this->set( [ 'data' => $return ] );
		$this->render( '/element/ajaxreturn', 'raw' );
	}

	/**
	 * Return filtered index table
	 */
	public function filter() {
		$allowed_fields = [ 'convar', 'breeder_variety_code' ];

        $this->paginate['sortableFields'] = [
            'convar',
            'official_name',
            'acronym',
            'created',
            'modified',
            'id',
        ];

        $this->paginate['fields'] = [
            'id',
            'convar',
            'official_name',
            'acronym',
            'created',
            'modified',
        ];

		if ( $this->request->is( 'get' )
		     && $this->request->is( 'ajax' )
		     && ! empty( $this->request->getQuery('fields') )
		     && array_intersect( $allowed_fields, $this->request->getQuery('fields') )
		) {
			$entries = $this->Varieties->filter( $this->request->getQuery('term') );
		} else {
			throw new \Exception( __( 'Direct access not allowed.' ) );
		}

		if ( $entries && $entries->count() ) {
            $this->Filter->setSortingParams();
            $this->Filter->setPaginationParams($entries);
			$varieties = $this->paginate( $entries );

			$this->set( compact( 'varieties' ) );
			$this->set( '_serialize', [ 'varieties' ] );
			$this->render( '/element/Variety/index_table', 'raw' );
		} else {
			$this->render( '/element/nothing_found', 'raw' );
		}
	}
}

