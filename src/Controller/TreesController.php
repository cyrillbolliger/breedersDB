<?php

namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Exception\Exception;

/**
 * Trees Controller
 *
 * @property \App\Model\Table\TreesTable $Trees
 */
class TreesController extends AppController {

	public $paginate = [
		'order' => [ 'modified' => 'desc' ],
		'limit' => 100,
	];

	public function initialize(): void {
		parent::initialize();
		$this->loadComponent( 'Brain' );
		$this->loadComponent( 'MarksReader' );
		$this->loadComponent( 'Filter' );
	}

	/**
	 * Index method
	 *
	 * @return \Cake\Network\Response|null
	 */
	public function index() {
		$this->paginate['contain'] = [
			'Rows',
		];

		$this->paginate['sortableFields'] = [
			'convar',
			'publicid',
			'row',
			'offset',
			'note',
			'date_eliminated',
			'modified',
			'id'
		];

		$this->paginate['fields'] = [
			'id',
			'publicid',
			'convar',
			'row'    => 'Rows.code',
			'offset',
			'note',
			'date_eliminated',
			'modified',
			'variety_id',
			'row_id',
		];

		$trees = $this->paginate(
		    $this->Trees->find( 'withEliminated', ['show_eliminated' => false] )
        );

		$this->set( compact( 'trees' ) );
		$this->set( '_serialize', [ 'trees' ] );
	}

	/**
	 * View method
	 *
	 * @param string|null $id Tree id.
	 *
	 * @return \Cake\Network\Response|null
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function view( $id = null ) {
		$tree  = $this->Trees->get( $id, [
			'contain' => [ 'Varieties', 'Rootstocks', 'Graftings', 'Rows', 'ExperimentSites', 'Marks' ]
		] );
		$marks = $this->MarksReader->get( $id, $tree->variety_id );
		$this->set( 'tree', $tree );
		$this->set( 'marks', $marks );
		$this->set( '_serialize', [ 'tree' ] );
	}

	/**
	 * Add method
	 *
	 * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$tree      = $this->Trees->newEmptyEntity();
		$varieties = array();

		if ( $this->request->is( 'post' ) ) {
			$session            = $this->request->getSession();
			$experiment_site_id = (int) $session->read( 'experiment_site_id' );
			$tree               = $this->Trees->patchEntity( $tree, $this->request->getData(),
				[ 'experiment_site_id' => $experiment_site_id ] );
			if ( $this->Trees->save( $tree ) ) {
				$this->Flash->success( __( 'The tree has been saved.' ) );

				return $this->redirect( [ 'action' => 'print', $tree->id, 'add' ] );
			} else {
				$this->Flash->error( __( 'The tree could not be saved. Please, try again.' ) );

				if ( $tree->variety_id ) {
					$varieties = $this->Trees->Varieties->getConvarList( $tree->variety_id );
				}
			}
		}

		$tree            = $this->Brain->remember( $tree );
		$rootstocks      = $this->Trees->Rootstocks->find( 'list' );
		$graftings       = $this->Trees->Graftings->find( 'list' );
		$rows            = $this->Trees->Rows->find( 'list' );
		$experimentSites = $this->Trees->ExperimentSites->find( 'list' );
		$this->set( compact( 'tree', 'varieties', 'rootstocks', 'graftings', 'rows', 'experimentSites' ) );
		$this->set( '_serialize', [ 'tree' ] );
	}

	/**
	 * Add method
	 *
	 * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
	 */
	public function addGenuineSeedling() {
		$tree      = $this->Trees->newEmptyEntity();
		$varieties = array();

		if ( $this->request->is( 'post' ) ) {
			$session            = $this->request->getSession();
			$experiment_site_id = (int) $session->read( 'experiment_site_id' );
			$tree               = $this->Trees->patchEntity( $tree, $this->request->getData(),
				[ 'experiment_site_id' => $experiment_site_id ] );
			if ( $this->Trees->save( $tree ) ) {
				$this->Flash->success( __( 'The tree has been saved.' ) );

				return $this->redirect( [ 'action' => 'print', $tree->id, 'addGenuineSeedling' ] );
			} else {
				$this->Flash->error( __( 'The tree could not be saved. Please, try again.' ) );

				if ( $tree->variety_id ) {
					$varieties = $this->Trees->Varieties->getConvarList( $tree->variety_id );
				}
			}
		}

		$tree            = $this->Brain->remember( $tree );
		$rootstocks      = $this->Trees->Rootstocks->find( 'list' );
		$graftings       = $this->Trees->Graftings->find( 'list' );
		$rows            = $this->Trees->Rows->find( 'list' );
		$experimentSites = $this->Trees->ExperimentSites->find( 'list' );
		$this->set( compact( 'tree', 'varieties', 'rootstocks', 'graftings', 'rows', 'experimentSites' ) );
		$this->set( '_serialize', [ 'tree' ] );
	}

	/**
	 * Add method
	 *
	 * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
	 */
	public function addGraftTree() {
		$tree      = $this->Trees->newEmptyEntity();
		$varieties = array();

		if ( $this->request->is( 'post' ) ) {
			$session            = $this->request->getSession();
			$experiment_site_id = (int) $session->read( 'experiment_site_id' );
			$tree               = $this->Trees->patchEntity( $tree, $this->request->getData(),
				[ 'experiment_site_id' => $experiment_site_id ] );
			if ( $this->Trees->save( $tree ) ) {
				$this->Flash->success( __( 'The tree has been saved.' ) );

				return $this->redirect( [ 'action' => 'print', $tree->id, 'addGraftTree' ] );
			} else {
				$this->Flash->error( __( 'The tree could not be saved. Please, try again.' ) );

				if ( $tree->variety_id ) {
					$varieties = $this->Trees->Varieties->getConvarList( $tree->variety_id );
				}
			}
		}

		$tree            = $this->Brain->remember( $tree );
		$rootstocks      = $this->Trees->Rootstocks->find( 'list' );
		$graftings       = $this->Trees->Graftings->find( 'list' );
		$rows            = $this->Trees->Rows->find( 'list' );
		$experimentSites = $this->Trees->ExperimentSites->find( 'list' );
		$this->set( compact( 'tree', 'varieties', 'rootstocks', 'graftings', 'rows', 'experimentSites' ) );
		$this->set( '_serialize', [ 'tree' ] );
	}

	/**
	 * Edit method
	 *
	 * @param string|null $id Tree id.
	 *
	 * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Network\Exception\NotFoundException When record not found.
	 */
	public function edit( $id = null ) {
		$tree = $this->Trees->get( $id, [
			'contain' => []
		] );

		$varieties = array();

		if ( $this->request->is( [ 'patch', 'post', 'put' ] ) ) {
			$data = $this->Trees->prefixPublicidOnElimination( $id, $this->request->getData());
			$tree = $this->Trees->patchEntity( $tree, $data );
			if ( $this->Trees->save( $tree ) ) {
				$this->Flash->success( __( 'The tree has been saved.' ) );

				return $this->redirect( [ 'action' => 'index' ] );
			} else {
				$this->Flash->error( __( 'The tree could not be saved. Please, try again.' ) );
			}
		}

		if ( $tree->variety_id ) {
			$varieties = $this->Trees->Varieties->getConvarList( $tree->variety_id );
		}

		$rootstocks      = $this->Trees->Rootstocks->find( 'list' );
		$graftings       = $this->Trees->Graftings->find( 'list' );
		$rows            = $this->Trees->Rows->find( 'list' );
		$experimentSites = $this->Trees->ExperimentSites->find( 'list' );
		$this->set( compact( 'tree', 'varieties', 'rootstocks', 'graftings', 'rows', 'experimentSites' ) );
		$this->set( '_serialize', [ 'tree' ] );
	}

	/**
	 * Plant method
	 */
	public function plant() {
		// just see the view
	}

	/**
	 * Eliminate method
	 */
	public function eliminate() {
		// just see the view
	}

    /**
     * Eliminate method
     */
    public function eliminateByScanner() {
        // just see the view
    }

	/**
	 * Return tree
	 */
	public function getTree() {
		$allowed_fields = [ 'publicid' ];

		if ( $this->request->is( 'get' )
		     && $this->request->is( 'ajax' )
		     && ! empty( $this->request->getQuery('fields') )
		     && array_intersect( $allowed_fields, $this->request->getQuery('fields') )
		) {
			$entries = $this->Trees->getByPublicId( $this->request->getQuery('term') );
		} else {
			throw new \Exception( __( 'Direct access not allowed.' ) );
		}

		if ( $entries ) {
			$tree = $entries;
			if ( $tree->variety_id ) {
				$varieties = $this->Trees->Varieties->getConvarList( $tree->variety_id );
			}
			$rootstocks      = $this->Trees->Rootstocks->find( 'list' );
			$graftings       = $this->Trees->Graftings->find( 'list' );
			$rows            = $this->Trees->Rows->find( 'list' );
			$experimentSites = $this->Trees->ExperimentSites->find( 'list' );

			$tree = $this->Brain->remember( $tree );

			$zpl = null;
			if ( ! empty( $this->request->getQuery('printable') ) && $this->request->getQuery('printable') != false ) {
				$zpl = $this->Trees->getLabelZpl( $tree->id, 'convar', true,
					$this->request->getSession()->read( 'time_zone' ) );
			}

			$this->set(
			    compact(
			        'zpl',
                    'tree',
                    'varieties',
                    'rootstocks',
                    'graftings',
                    'rows',
				    'experimentSites'
                )
            );
			$this->set( '_serialize', [ 'tree' ] );

			$this->render( '/element/Tree/' . (string) $this->request->getQuery('element') );
		} else {
			$this->response->withStatus( 204 );
			$this->render( '/element/nothing_found' );
		}
	}

	/**
	 * Delete method
	 *
	 * @param string|null $id Tree id.
	 *
	 * @return \Cake\Network\Response|null Redirects to index.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function delete( $id = null ) {
		$this->request->allowMethod( [ 'post', 'delete' ] );
		$tree = $this->Trees->get( $id );
		if ( $this->Trees->delete( $tree ) ) {
			$this->Flash->success( __( 'The tree has been deleted.' ) );
		} else {
			$this->Flash->error( __( 'The tree could not be deleted. Please, try again.' ) );
		}

		return $this->redirect( [ 'action' => 'index' ] );
	}

	/**
	 * Return filtered index table
	 */
	public function filter() {
		$allowed_fields = [ 'publicid', 'convar' ];

		$this->paginate['contain'] = [
			'Rows',
		];

		$this->paginate['sortableFields'] = [
			'convar',
			'publicid',
			'row',
			'offset',
			'note',
			'date_eliminated',
			'modified',
			'id'
		];

		$this->paginate['fields'] = [
			'id',
			'publicid',
			'convar',
			'row'    => 'Rows.code',
			'offset',
			'note',
			'date_eliminated',
			'modified',
			'variety_id',
			'row_id',
		];

		if ( $this->request->is( 'get' )
		     && $this->request->is( 'ajax' )
		     && ! empty( $this->request->getQuery('fields') )
		     && array_intersect( $allowed_fields, $this->request->getQuery('fields') )
		) {
		    $term = $this->request->getQuery('term');
			$entries = $this->Trees->filter( $term );

			if ( $entries && $entries->count() ) {
                $show_eliminated = filter_var( $this->request->getQuery('options.show_eliminated', false), FILTER_VALIDATE_BOOL);
                $entries = $entries->find('withEliminated', ['show_eliminated' => $show_eliminated]);
            }
		} else {
			throw new \Exception( __( 'Direct access not allowed.' ) );
		}

		if ( $entries && $entries->count() ) {
            $this->Filter->setSortingParams();
            $this->Filter->setPaginationParams($entries);
			$trees = $this->paginate( $entries );

			$this->set( compact( 'trees' ) );
			$this->set( '_serialize', [ 'trees' ] );
			$this->render( '/element/Tree/index_table' );
		} else {
			$this->render( '/element/nothing_found' );
		}
	}

	/**
	 * Return list with code (convar) as value and id as key.
	 * Must be called as ajax get request.
	 */
	public function searchTrees() {

		if ( $this->request->is( 'get' )
		     && $this->request->is( 'ajax' )
		     && ! empty( $this->request->getQuery('term') )
		) {
			$entries = $this->Trees->filter( $this->request->getQuery('term') );
		} else {
			throw new \Exception( __( 'Direct access not allowed.' ) );
		}

		$return = array();
		if ( $entries ) {
			foreach ( $entries as $entry ) {
				$tree                 = $this->Trees->getIdPublicidAndConvarList( $entry->id );
				$return[ $entry->id ] = $tree[ $entry->id ];
			}
		}

		$this->set( [ 'data' => $return ] );
		$this->render( '/element/ajaxreturn' );
	}

	/**
	 * Show the print dialog
	 *
	 * @param int|string $tree_id
	 * @param string $caller action to redirect after printing
	 * @param mixed $params for action
	 */
	public function print( $tree_id, string $caller, $params = null ) {
	    $tree_id = (int) $tree_id;

		$convar_zpl               = $this->Trees->getLabelZpl( $tree_id, 'convar' );
		$breeder_variety_code_zpl = $this->Trees->getLabelZpl( $tree_id, 'breeder_variety_code' );

		$this->set( [
			'buttons'    => [
				'regular'   => [
					'label' => __( 'Regular' ),
					'zpl'   => $convar_zpl,
				],
				'anonymous' => [
					'label' => __( 'Anonymous' ),
					'zpl'   => $breeder_variety_code_zpl
				],
			],
			'focus'      => 'regular',
			'controller' => 'Trees',
			'action'     => $caller,
			'params'     => $params,
			'nav'        => 'Tree/nav'
		] );
		$this->render( '/element/print' );
	}
}
