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
		
		$query->query = json_decode( $query->query );
		
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
	 * Add method
	 *
	 * @param int $query_group_id Query group the query will be added to
	 *
	 * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
	 */
	public function add( $query_group_id ) {
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
		
		$views             = $this->Queries->getViewNames();
		$view_fields       = $this->Queries->getTranslatedFieldsOf( array_keys( $views ) );
		$default_root_view = 'MarksView';
		$root_view         = $default_root_view;
		
		$active_views  = array(); // ToDo: Get good defaults
		$active_fields = array(); // ToDo: Get good defaults
		
		$associations = array();
		foreach ( array_keys( $views ) as $view_name ) {
			$associations[ $view_name ] = $this->Queries->getAssociationsOf( $view_name );
		}
		
		$filter_data = $this->Queries->getFilterData();
		$where_rules = json_encode( null );
		
		$this->loadModel( 'QueryGroups' );
		$queryGroups  = $this->QueryGroups->find( 'all' )->contain( 'Queries' )->order( 'code' );
		$query_groups = $this->QueryGroups->find( 'list' )->order( 'code' );
		
		$this->set( compact(
			'default_root_view',
			'root_view',
			'query_group_id',
			'query',
			'query_groups',
			'queryGroups',
			'views',
			'view_fields',
			'associations',
			'active_views',
			'active_fields',
			'filter_data',
			'where_rules'
		) );
		$this->set( '_serialize', [
			'default_root_view',
			'root_view',
			'query_group_id',
			'query',
			'query_groups',
			'queryGroups',
			'views',
			'view_fields',
			'associations',
			'active_views',
			'active_fields',
			'filter_data',
			'where_rules'
		] );
	}
	
	/**
	 * Add mark query method
	 *
	 * @param int $query_group_id Query group the query will be added to
	 *
	 * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
	 */
	public function addMarkQuery( $query_group_id ) {
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
		
		$mark_selectors                            = $this->Queries->getMarksSelectorData();
		$breeding_object_aggregation_modes        = $this->Queries->getBreedingObjectAggregationModes();
		$default_breeding_object_aggregation_mode = 'convar';
		
		$views             = $this->Queries->getViewNames();
		$view_fields       = $this->Queries->getTranslatedFieldsOf( array_keys( $views ) );
		$default_root_view = 'MarksView';
		$root_view         = $default_root_view;
		
		$active_views  = array(); // ToDo: Get good defaults
		$active_fields = array(); // ToDo: Get good defaults
		
		$associations = array();
		foreach ( array_keys( $views ) as $view_name ) {
			$associations[ $view_name ] = $this->Queries->getAssociationsOf( $view_name );
		}
		
		$filter_data = $this->Queries->getFilterData();
		
		$where_rules = json_encode( null );
		
		$this->loadModel( 'QueryGroups' );
		$queryGroups  = $this->QueryGroups->find( 'all' )->contain( 'Queries' )->order( 'code' );
		$query_groups = $this->QueryGroups->find( 'list' )->order( 'code' );
		
		$this->set( compact(
			'default_root_view',
			'root_view',
			'query_group_id',
			'query',
			'query_groups',
			'queryGroups',
			'views',
			'view_fields',
			'associations',
			'active_views',
			'active_fields',
			'filter_data',
			'where_rules',
			'breeding_object_aggregation_modes',
			'default_breeding_object_aggregation_mode',
			'mark_selectors'
		) );
		$this->set( '_serialize', [
			'default_root_view',
			'root_view',
			'query_group_id',
			'query',
			'query_groups',
			'queryGroups',
			'views',
			'view_fields',
			'associations',
			'active_views',
			'active_fields',
			'filter_data',
			'where_rules',
			'breeding_object_aggregation_modes',
			'default_breeding_object_aggregation_mode',
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
		
		$q = json_decode( $query->query );
		
		$views             = $this->Queries->getViewNames();
		$view_fields       = $this->Queries->getTranslatedFieldsOf( array_keys( $views ) );
		$default_root_view = $q->root_view;
		
		$active_views  = $this->Queries->getActiveViewTables( $q );
		$active_fields = $this->Queries->getActiveFields( $q );
		
		$associations = array();
		foreach ( array_keys( $views ) as $view_name ) {
			$associations[ $view_name ] = $this->Queries->getAssociationsOf( $view_name );
		}
		
		$filter_data = $this->Queries->getFilterData();
		$where_rules = $this->Queries->getWhereRules( $q );
		
		$this->loadModel( 'QueryGroups' );
		$queryGroups  = $this->QueryGroups->find( 'all' )->contain( 'Queries' )->order( 'code' );
		$query_groups = $this->QueryGroups->find( 'list' )->order( 'code' );
		
		$this->set( compact(
			'query',
			'query_groups',
			'queryGroups',
			'views',
			'default_root_view',
			'view_fields',
			'active_views',
			'active_fields',
			'associations',
			'filter_data',
			'where_rules'
		) );
		$this->set( '_serialize', [
			'query',
			'query_groups',
			'queryGroups',
			'views',
			'default_root_view',
			'view_fields',
			'active_views',
			'active_fields',
			'associations',
			'filter_data',
			'where_rules'
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
	
	
	public function viewMarkQuery() {
		$orderBy    = [ 'sort' => 'id', 'direction' => 'asc' ];
		$clearCache = false;
		$mode       = 'varieties';
		
		// todo: see view $columns
		$display = [];
		
		$properties = [
			'H_Gesamteindruck Frucht',
			'K1_Schorf Blatt',
		];
		
		$data = $this->Queries->customFindMarks( $mode, $display, $properties, $clearCache, $orderBy );
		
		$results = $data->take( 20, 0 );
		
		$regular_columns = [
			'convar' => $this->Queries->translateFields( 'VarietiesView.convar' ),
		];
		
		$mark_columns = [];
		foreach ( $properties as $property ) {
			$markProperties = TableRegistry::get( 'MarkFormProperties' );
			$markProperty   = $markProperties->find()->where( [ 'name' => $property ] )->firstOrFail();
			
			$mark_columns[ $property ] = (object) [
				'name'       => $property,
				'aggregated' => in_array( $markProperty->field_type, [ 'INTEGER', 'FLOAT' ] ),
				'max'        => (float) $markProperty->validation_rule['max'],
				'min'        => (float) $markProperty->validation_rule['min'],
				'display'    => 'median', // todo: add correct field
			];
		}
		
		
		$this->loadModel( 'QueryGroups' );
		$queryGroups  = $this->QueryGroups->find( 'all' )->contain( 'Queries' )->order( 'code' );
		$query_groups = $this->QueryGroups->find( 'list' )->order( 'code' );
		
		$this->set( compact( 'query', 'query_groups', 'queryGroups', 'results', 'regular_columns', 'mark_columns' ) );
		$this->set( '_serialize',
			[ 'query', 'query_groups', 'queryGroups', 'results', 'regular_columns', 'mark_columns' ] );
	}
}
