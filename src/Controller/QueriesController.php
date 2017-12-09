<?php

namespace App\Controller;

use Cake\ORM\TableRegistry;

/**
 * Queries Controller
 *
 * @property \App\Model\Table\QueriesTable $Queries
 */
class QueriesController extends AppController {
	public function initialize() {
		parent::initialize();
		
		$this->loadComponent( 'Excel' );
	}
	
	/**
	 * Index method
	 *
	 * @return \Cake\Network\Response|null
	 */
	public function index() {
		$this->loadModel( 'QueryGroups' );
		$queryGroups = $this->QueryGroups->find( 'all' )->contain( 'Queries' )->order( 'code' );
		
		$this->set( compact( 'queryGroups' ) );
		$this->set( '_serialize', [ 'queryGroups' ] );
	}
	
	/**
	 * View method
	 *
	 * @param string|null $id Query id.
	 *
	 * @return \Cake\Network\Response|null
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function view( $id = null ) {
		$query = $this->Queries->get( $id, [
			'contain' => [ 'QueryGroups' ]
		] );
		
		if ('MarksView' === $query->query->root_view) {
			return $this->redirect( [ 'action' => 'viewMarkQuery', $id ] );
		}
		
		$q       = $this->Queries->buildViewQuery( $query->query );
		$results = $this->paginate( $q );
		
		$columns = $this->Queries->getViewQueryColumns( $query->query );
		
		$this->loadModel( 'QueryGroups' );
		$queryGroups  = $this->QueryGroups->find( 'all' )->contain( 'Queries' )->order( 'code' );
		$query_groups = $this->QueryGroups->find( 'list' )->order( 'code' );
		
		$this->set( compact( 'query', 'query_groups', 'queryGroups', 'results', 'columns' ) );
		$this->set( '_serialize', [ 'query', 'query_groups', 'queryGroups', 'results', 'columns' ] );
	}
	
	/**
	 * Export method
	 *
	 * @param string|null $id Query id.
	 *
	 * @return \Cake\Network\Response|null
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function export( $id = null ) {
		$query        = $this->Queries->get( $id );
		$query->query = json_decode( $query->query );
		
		$q       = $this->Queries->buildViewQuery( $query->query );
		$columns = $this->Queries->getViewQueryColumns( $query->query );
		$file    = $this->Excel->export( $q, $columns, $query->code );
		
		$this->response->type( 'xlsx' );
		$this->response->file( $file, [ 'download' => true ] );
		
		// used for jquery.fileDownload.js
		$this->Cookie->configKey( 'fileDownload', 'encryption', false );
		$this->Cookie->write( 'fileDownload', 'true' );
		
		// prevent rendering
		$this->autoRender = false;
	}
	
	/**
	 * Add query method
	 *
	 * @param int $query_group_id Query group the query will be added to
	 *
	 * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
	 */
	public function add( int $query_group_id ) {
		$query = $this->Queries->newEntity();
		if ( $this->request->is( 'post' ) ) {
			$query = $this->Queries->patchEntityWithQueryData( $query, $this->request->data );
			if ( $this->Queries->save( $query ) ) {
				$this->Flash->success( __( 'The query has been saved.' ) );
				
				return $this->redirect( [ 'action' => 'view', $query->id ] );
			} else {
				$this->Flash->error( __( 'The query could not be saved. Please, try again.' ) );
			}
		}
		
		$markProperties = TableRegistry::get( 'MarkFormProperties' );
		$mark_selectors = $markProperties->find( 'all' )->order( [ 'name' => 'asc' ] );
		
		$views       = $this->Queries->getViewNames();
		$view_fields = $this->Queries->getTranslatedFieldsOf( array_keys( $views ) );
		
		$active_views          = [];
		$active_regular_fields = [];
		$mark_fields    = [];
		
		$associations = [];
		foreach ( array_keys( $views ) as $view_name ) {
			$associations[ $view_name ] = $this->Queries->getAssociationsOf( $view_name );
		}
		
		$filter_data = $this->Queries->getFilterData();
		
		$where_rules = json_encode( null );
		
		$this->loadModel( 'QueryGroups' );
		$queryGroups  = $this->QueryGroups->find( 'all' )->contain( 'Queries' )->order( 'code' );
		$query_groups = $this->QueryGroups->find( 'list' )->order( 'code' );
		
		$this->set( compact(
			'query_group_id',
			'query',
			'query_groups',
			'queryGroups',
			'views',
			'view_fields',
			'associations',
			'active_views',
			'active_regular_fields',
			'mark_fields',
			'filter_data',
			'where_rules',
			'mark_selectors'
		) );
		$this->set( '_serialize', [
			'query_group_id',
			'query',
			'query_groups',
			'queryGroups',
			'views',
			'view_fields',
			'associations',
			'active_views',
			'active_regular_fields',
			'mark_fields',
			'filter_data',
			'where_rules',
			'mark_selectors'
		] );
	}
	
	/**
	 * Edit method
	 *
	 * @param string|null $id Query id.
	 *
	 * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Network\Exception\NotFoundException When record not found.
	 */
	public function edit( $id = null ) {
		$query = $this->Queries->get( $id, [
			'contain' => []
		] );
		if ( $this->request->is( [ 'patch', 'post', 'put' ] ) ) {
			$query = $this->Queries->patchEntityWithQueryData( $query, $this->request->data );
			if ( $this->Queries->save( $query ) ) {
				$this->Flash->success( __( 'The query has been saved.' ) );
				
				return $this->redirect( [ 'action' => 'view', $id ] );
			} else {
				$this->Flash->error( __( 'The query could not be saved. Please, try again.' ) );
			}
		}
		
		$markProperties = TableRegistry::get( 'MarkFormProperties' );
		$mark_selectors = $markProperties->find( 'all' )->order( [ 'name' => 'asc' ] );
		
		$views       = $this->Queries->getViewNames();
		$view_fields = $this->Queries->getTranslatedFieldsOf( array_keys( $views ) );
		$mark_fields = $query->mark_fields;
		
		$active_views          = $query->active_view_tables;
		$active_regular_fields = $query->active_regular_fields;
		
		$associations = array();
		foreach ( array_keys( $views ) as $view_name ) {
			$associations[ $view_name ] = $this->Queries->getAssociationsOf( $view_name );
		}
		
		$filter_data = $this->Queries->getFilterData();
		$where_rules = $query->where_rules_json;
		
		$this->loadModel( 'QueryGroups' );
		$queryGroups  = $this->QueryGroups->find( 'all' )->contain( 'Queries' )->order( 'code' );
		$query_groups = $this->QueryGroups->find( 'list' )->order( 'code' );
		
		$this->set( compact(
			'query',
			'query_groups',
			'queryGroups',
			'views',
			'view_fields',
			'active_views',
			'active_regular_fields',
			'mark_fields',
			'associations',
			'filter_data',
			'where_rules',
			'mark_selectors'
		) );
		$this->set( '_serialize', [
			'query',
			'query_groups',
			'queryGroups',
			'views',
			'view_fields',
			'active_views',
			'active_regular_fields',
			'mark_fields',
			'associations',
			'filter_data',
			'where_rules',
			'mark_selectors'
		] );
	}
	
	/**
	 * Delete method
	 *
	 * @param string|null $id Query id.
	 *
	 * @return \Cake\Network\Response|null Redirects to index.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function delete( $id = null ) {
		$this->request->allowMethod( [ 'post', 'delete' ] );
		$query = $this->Queries->get( $id );
		if ( $this->Queries->delete( $query ) ) {
			$this->Flash->success( __( 'The query has been deleted.' ) );
		} else {
			$this->Flash->error( __( 'The query could not be deleted. Please, try again.' ) );
		}
		
		return $this->redirect( [ 'action' => 'index' ] );
	}
	
	
	public function viewMarkQuery( int $id, bool $clearCache = false ) {
		// get query
		$query = $this->Queries->get( $id );
		
		// set order
		$orderBy = [ 'sort' => 'id', 'direction' => 'asc' ];
		
		// query the data
		$marksViewTable = TableRegistry::get( 'MarksView' );
		$data           = $marksViewTable->customFindMarks(
			$query->breeding_object_aggregation_mode,
			$query->active_regular_fields,
			$query->active_mark_field_ids,
			$query->regular_conditions,
			$query->mark_conditions,
			$clearCache,
			$orderBy
		);
		
		// set pagination
		$results = $data->take( 20, 0 );
		
		// set regular columns
		$regular_columns = [];
		foreach ( $query->active_regular_fields as $field ) {
			$key                     = explode( '.', $field )[1];
			$regular_columns[ $key ] = $this->Queries->translateFields( $field );
		}
		
		// set mark columns
		$markProperties = TableRegistry::get( 'MarkFormProperties' );
		$mark_columns   = [];
		foreach ( $query->active_mark_field_ids as $property_id ) {
			$mark_columns[ $property_id ] = $markProperties->get( $property_id );
		}
		
		// get navigation stuff
		$this->loadModel( 'QueryGroups' );
		$queryGroups  = $this->QueryGroups->find( 'all' )->contain( 'Queries' )->order( 'code' );
		$query_groups = $this->QueryGroups->find( 'list' )->order( 'code' );
		
		// set view vars
		$this->set( compact( 'query', 'query_groups', 'queryGroups', 'results', 'regular_columns', 'mark_columns' ) );
		$this->set( '_serialize',
			[ 'query', 'query_groups', 'queryGroups', 'results', 'regular_columns', 'mark_columns' ] );
	}
}
