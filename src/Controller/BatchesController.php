<?php

namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Exception\Exception;

/**
 * Batches Controller
 *
 * @property \App\Model\Table\BatchesTable $Batches
 */
class BatchesController extends AppController {
	public $paginate = [
		'order' => [ 'modified' => 'desc' ],
		'limit' => 100,
	];

	public function initialize(): void {
		parent::initialize();
		$this->loadComponent( 'MarksReader' );
        $this->loadComponent( 'Filter' );
	}

	/**
	 * Index method
	 *
	 * @return \Cake\Network\Response|null
	 */
	public function index() {
		$this->paginate['contain'] = [ 'Crossings' ];

		$this->paginate['sortWhitelist'] = [
			'crossing_batch',
			'date_sowed',
			'seed_tray',
			'date_planted',
			'patch',
			'modified',
			'id',
		];

		$this->paginate['fields'] = [
			'id',
			'crossing_batch' => $this->Batches
				->find()
				->func()
				->concat( [
					'Crossings.code' => 'literal',
					'Batches.code'   => 'literal',
				] ),
			'date_sowed',
			'seed_tray',
			'date_planted',
			'patch',
			'code',
			'Crossings.code',
		];

		$batches = $this->paginate( $this->Batches->find('withoutOfficialVarieties') );

		$this->set( compact( 'batches' ) );
		$this->set( '_serialize', [ 'batches' ] );
	}

	/**
	 * View method
	 *
	 * @param string|null $id Batch id.
	 *
	 * @return \Cake\Network\Response|null
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function view( $id = null ) {
		$batch = $this->Batches->get( $id, [
			'contain' => [ 'Crossings', 'Marks', 'Varieties' ]
		] );

		$marks = $this->MarksReader->get( null, null, $id );
		$this->set( 'marks', $marks );
		$this->set( 'batch', $batch );
		$this->set( '_serialize', [ 'batch' ] );
	}

	/**
	 * Add method
	 *
	 * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$batch = $this->Batches->newEntity();
		if ( $this->request->is( 'post' ) ) {
			$batch = $this->Batches->patchEntity( $batch, $this->request->getData());
			if ( $this->Batches->save( $batch ) ) {
				$this->Flash->success( __( 'The batch has been saved.' ) );

				return $this->redirect( [ 'action' => 'print', $batch->id, 'index' ] );
			} else {
				$this->Flash->error( __( 'The batch could not be saved. Please, try again.' ) );
			}
		}
		$crossings = $this->Batches->Crossings->find( 'list' )->where( [ 'id <>' => 1 ] );
		$this->set( compact( 'batch', 'crossings' ) );
		$this->set( '_serialize', [ 'batch' ] );
	}

	/**
	 * Edit method
	 *
	 * @param string|null $id Batch id.
	 *
	 * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Network\Exception\NotFoundException When record not found.
	 */
	public function edit( $id = null ) {
		$batch = $this->Batches->get( $id, [
			'contain' => []
		] );
		if ( $this->request->is( [ 'patch', 'post', 'put' ] ) ) {
			$batch = $this->Batches->patchEntity( $batch, $this->request->getData());
			if ( $this->Batches->save( $batch ) ) {
				$this->Flash->success( __( 'The batch has been saved.' ) );

				return $this->redirect( [ 'action' => 'index' ] );
			} else {
				$this->Flash->error( __( 'The batch could not be saved. Please, try again.' ) );
			}
		}
		$crossings = $this->Batches->Crossings->find( 'list' )->where( [ 'id <>' => 1 ] );
		$this->set( compact( 'batch', 'crossings' ) );
		$this->set( '_serialize', [ 'batch' ] );
	}

	/**
	 * Delete method
	 *
	 * @param string|null $id Batch id.
	 *
	 * @return \Cake\Network\Response|null Redirects to index.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function delete( $id = null ) {
		$this->request->allowMethod( [ 'post', 'delete' ] );
		$batch = $this->Batches->get( $id );
		if ( $this->Batches->delete( $batch ) ) {
			$this->Flash->success( __( 'The batch has been deleted.' ) );
		} else {
			$this->Flash->error( __( 'The batch could not be deleted. Please, try again.' ) );
		}

		return $this->redirect( [ 'action' => 'index' ] );
	}

	/**
	 * Return filtered index table
	 */
	public function filter() {
		$allowed_fields = [ 'crossing_batch' ];

		if ( $this->request->is( 'get' )
		     && $this->request->is( 'ajax' )
		     && ! empty( $this->request->getQuery('fields') )
		     && array_intersect( $allowed_fields, $this->request->getQuery('fields') )
		) {
			$entries = $this->Batches->filterCrossingBatches( $this->request->getQuery('term') )
                                     ->find('withoutOfficialVarieties');
		} else {
			throw new Exception( __( 'Direct access not allowed.' ) );
		}

		if ( $entries && $entries->count() ) {
            $this->Filter->setSortingParams();
            $this->Filter->setPaginationParams($entries);
			$batches = $this->paginate( $entries );

			$this->set( compact( 'batches' ) );
			$this->set( '_serialize', [ 'batches' ] );
			$this->render( '/element/Batch/index_table' );
		} else {
			$this->render( '/element/nothing_found' );
		}
	}

	/**
	 * Show the print dialog
	 *
	 * @param int $batch_id
	 * @param string $caller action to redirect after printing
	 * @param mixed $params for action
	 */
	public function print( int $batch_id, string $caller, $params = null ) {
		$zpl = $this->Batches->getLabelZpl( $batch_id );

		$this->set( [
			'buttons'    => [
				'regular' => [
					'label' => __( 'Regular' ),
					'zpl'   => $zpl,
				],
			],
			'focus'      => 'regular',
			'controller' => 'Batches',
			'action'     => $caller,
			'params'     => $params,
			'nav'        => 'Batch/nav'
		] );
		$this->render( '/element/print' );
	}
}
