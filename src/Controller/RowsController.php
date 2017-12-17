<?php

namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Exception\Exception;

/**
 * Rows Controller
 *
 * @property \App\Model\Table\RowsTable $Rows
 */
class RowsController extends AppController {
	
	public $paginate = [
		'order' => [ 'modified' => 'desc' ],
	];
	
	/**
	 * Index method
	 *
	 * @return \Cake\Network\Response|null
	 */
	public function index() {
		$rows = $this->paginate( $this->Rows );
		
		$this->set( compact( 'rows' ) );
		$this->set( '_serialize', [ 'rows' ] );
	}
	
	/**
	 * View method
	 *
	 * @param string|null $id Row id.
	 *
	 * @return \Cake\Network\Response|null
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function view( $id = null ) {
		$row = $this->Rows->get( $id, [
			'contain' => [ 'Trees' ]
		] );
		
		$this->set( 'row', $row );
		$this->set( '_serialize', [ 'row' ] );
	}
	
	/**
	 * Add method
	 *
	 * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$row = $this->Rows->newEntity();
		if ( $this->request->is( 'post' ) ) {
			$row = $this->Rows->patchEntity( $row, $this->request->data );
			if ( $this->Rows->save( $row ) ) {
				$this->Flash->success( __( 'The row has been saved.' ) );
				
				return $this->redirect( [ 'action' => 'index' ] );
			} else {
				$this->Flash->error( __( 'The row could not be saved. Please, try again.' ) );
			}
		}
		$this->set( compact( 'row' ) );
		$this->set( '_serialize', [ 'row' ] );
	}
	
	/**
	 * Edit method
	 *
	 * @param string|null $id Row id.
	 *
	 * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Network\Exception\NotFoundException When record not found.
	 */
	public function edit( $id = null ) {
		$row = $this->Rows->get( $id, [
			'contain' => []
		] );
		if ( $this->request->is( [ 'patch', 'post', 'put' ] ) ) {
			$row = $this->Rows->patchEntity( $row, $this->request->data );
			if ( $this->Rows->save( $row ) ) {
				$this->Flash->success( __( 'The row has been saved.' ) );
				
				return $this->redirect( [ 'action' => 'index' ] );
			} else {
				$this->Flash->error( __( 'The row could not be saved. Please, try again.' ) );
			}
		}
		$this->set( compact( 'row' ) );
		$this->set( '_serialize', [ 'row' ] );
	}
	
	/**
	 * Delete method
	 *
	 * @param string|null $id Row id.
	 *
	 * @return \Cake\Network\Response|null Redirects to index.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function delete( $id = null ) {
		$this->request->allowMethod( [ 'post', 'delete' ] );
		$row = $this->Rows->get( $id );
		if ( $this->Rows->delete( $row ) ) {
			$this->Flash->success( __( 'The row has been deleted.' ) );
		} else {
			$this->Flash->error( __( 'The row could not be deleted. Please, try again.' ) );
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
		     && ! empty( $this->request->query['fields'] )
		     && array_intersect( $allowed_fields, $this->request->query['fields'] )
		) {
			$entries = $this->Rows->filter( $this->request->query['term'] );
			
			if ( ! empty( $this->request->query['sort'] ) ) {
				$sort                    = $this->request->query['sort'];
				$direction               = empty( $this->request->query['direction'] ) ? 'asc' : $this->request->query['direction'];
				$this->paginate['order'] = [ $sort => $direction ];
			}
			if ( ! empty( $this->request->query['page'] ) ) {
				$this->paginate['page'] = $this->request->query['page'];
			}
			
		} else {
			throw new Exception( __( 'Direct access not allowed.' ) );
		}
		
		if ( $entries->count() ) {
			$rows = $this->paginate( $entries );
			$this->set( compact( 'rows' ) );
			$this->set( '_serialize', [ 'rows' ] );
			$this->render( '/Element/Row/index_table' );
		} else {
			$this->render( '/Element/nothing_found' );
		}
	}
}
