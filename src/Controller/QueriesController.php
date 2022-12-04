<?php

namespace App\Controller;

use App\Controller\Component\CollectionPaginatorComponent;
use App\Controller\Component\ExcelComponent;
use App\Model\Table\MarksViewTable;
use Cake\Http\Cookie\Cookie;

/**
 * Queries Controller
 *
 * @property \App\Model\Table\QueriesTable $Queries
 * @property ExcelComponent $Excel
 * @property CollectionPaginatorComponent $CollectionPaginator
 */
class QueriesController extends AppController {
    public $paginate = [
        'limit'    => 250,
        'maxLimit' => 500,
    ];

    public function initialize(): void {
        parent::initialize();

        $this->loadComponent( 'Excel' );
        $this->loadComponent( 'CollectionPaginator' );
    }

    /**
     * Index method
     *
     * @return void
     */
    public function index() {
        $queryGroups = $this->fetchTable( 'QueryGroups' )->find( 'all' )->contain( 'Queries' )->order( 'code' );

        $this->set( compact( 'queryGroups' ) );
        $this->set( '_serialize', [ 'queryGroups' ] );
    }

    /**
     * View method
     *
     * If the query with the given id appears to be a mark query,
     * the user will be redirected to the self::viewMarkQuery function
     *
     * @param string|null $id Query id.
     *
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     * @throws \Exception if association type of selected tables is not implemented
     */
    public function view( $id = null ) {
        $query = $this->Queries->get( $id, [
            'contain' => [ 'QueryGroups' ]
        ] );

        if ( 'MarksView' === $query->query->root_view ) {
            return $this->redirect( [ 'action' => 'viewMarkQuery', $id ] );
        }

        $q       = $this->Queries->buildViewQuery( $query );
        $results = $this->paginate( $q );

        $columns = $this->Queries->getViewQueryColumns( $query );

        $this->QueryGroups = $this->fetchTable( 'QueryGroups' );
        $queryGroups  = $this->QueryGroups->find( 'all' )->contain( 'Queries' )->order( 'code' );
        $query_groups = $this->QueryGroups->find( 'list' )->order( 'code' );

        $this->set( compact( 'query', 'query_groups', 'queryGroups', 'results', 'columns' ) );
        $this->set( '_serialize', [ 'query', 'query_groups', 'queryGroups', 'results', 'columns' ] );
    }

    /**
     * View mark query method
     *
     * @param int|string $id
     *
     * @throws \Exception
     */
    public function viewMarkQuery( $id ) {
        // get query
        $query = $this->Queries->get( (int) $id );

        // query the data
        /** @var MarksViewTable $marksViewTable */
        $marksViewTable = $this->getTableLocator()->get( 'MarksView' );
        $data           = $marksViewTable->customFindMarks(
            $query->breeding_object_aggregation_mode,
            $query->active_regular_fields,
            $query->active_mark_field_ids,
            $query->regular_conditions,
            $query->mark_conditions
        );

        // get paginated results
        $results = $this->CollectionPaginator->paginate( $data, [ $marksViewTable, 'sort' ] );

        // get columns
        $regular_columns = $this->Queries->getRegularColumns( $query );
        $mark_columns    = $this->Queries->getMarkColumns( $query );

        // get navigation stuff
        $this->QueryGroups = $this->fetchTable( 'QueryGroups' );
        $queryGroups  = $this->QueryGroups->find( 'all' )->contain( 'Queries' )->order( 'code' );
        $query_groups = $this->QueryGroups->find( 'list' )->order( 'code' );

        // set view vars
        $this->set( compact(
            'query',
            'query_groups',
            'queryGroups',
            'results',
            'regular_columns',
            'mark_columns'
        ) );
        $this->set( '_serialize', [
            'query',
            'query_groups',
            'queryGroups',
            'results',
            'regular_columns',
            'mark_columns'
        ] );
    }

    /**
     * Export method
     *
     * @param string|null $id Query id.
     *
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     * @throws \Exception if association type of selected tables is not implemented
     */
    public function export( $id = null ) {
        $file = $this->_getExportFile( $id );

        $this->response = $this->response->withType( 'xlsx' );
        $this->response = $this->response->withFile( $file, [ 'download' => true ] );

        // used for jquery.fileDownload.js
        $this->getResponse()->withCookie(
            new Cookie('fileDownload', 'true', null, null, null, false)
        );

        // prevent rendering
        $this->autoRender = false;
    }

    /**
     * Export the results of a query into a excel file and return the path to it
     *
     * @param int $id of the query who's data shall be exported
     *
     * @return string with the path to the exported file
     * @throws \Exception
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    private function _getExportFile( int $id ): string {
        $query = $this->Queries->get( $id );

        // if its a regular query
        if ( 'MarksView' !== $query->query->root_view ) {
            $q       = $this->Queries->buildViewQuery( $query );
            $columns = $this->Queries->getViewQueryColumns( $query );

            return $this->Excel->exportFromQuery( $q, $columns, $query->code );
        }

        // -- if its a mark query --
        // query the data
        $marksViewTable = $this->getTableLocator()->get( 'MarksView' );
        $data           = $marksViewTable->customFindMarks(
            $query->breeding_object_aggregation_mode,
            $query->active_regular_fields,
            $query->active_mark_field_ids,
            $query->regular_conditions,
            $query->mark_conditions
        );

        // get columns
        $regular_columns = $this->Queries->getRegularColumns( $query );
        $mark_columns    = $this->Queries->getMarkColumns( $query );

        return $this->Excel->exportFromMarkCollection( $data, $regular_columns, $mark_columns, $query->code );
    }

    /**
     * Add query method
     *
     * @param int|string $query_group_id Query group the query will be added to
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     *
     * @throws \Exception if any filter data fields validator type is unknown.
     */
    public function add( $query_group_id ) {
        $query_group_id = (int) $query_group_id;

        $query = $this->Queries->newEmptyEntity();
        if ( $this->request->is( 'post' ) ) {
            $query = $this->Queries->patchEntityWithQueryData( $query, $this->request->getData());
            if ( $this->Queries->save( $query ) ) {
                $this->Flash->success( __( 'The query has been saved.' ) );

                return $this->redirect( [ 'action' => 'view', $query->id ] );
            } else {
                $this->Flash->error( __( 'The query could not be saved. Please, try again.' ) );
            }
        }

        $markProperties = $this->getTableLocator()->get( 'MarkFormProperties' );
        $mark_selectors = $markProperties->find( 'all' )->order( [ 'name' => 'asc' ] );

        $views       = $this->Queries->getViewNames();
        $view_fields = $this->Queries->getTranslatedFieldsOf( array_keys( $views ) );

        $active_views          = [];
        $active_regular_fields = [];
        $mark_fields           = [];

        $associations = [];
        foreach ( array_keys( $views ) as $view_name ) {
            $associations[ $view_name ] = $this->Queries->getAssociationsOf( $view_name );
        }

        $filter_data = $this->Queries->getFilterData();

        $where_rules = null;

        $this->QueryGroups = $this->fetchTable( 'QueryGroups' );
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
     *
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     * @throws \Exception if any filter data fields validator type is unknown.
     */
    public function edit( $id = null ) {
        $query = $this->Queries->get( $id, [
            'contain' => []
        ] );
        if ( $this->request->is( [ 'patch', 'post', 'put' ] ) ) {
            $query = $this->Queries->patchEntityWithQueryData( $query, $this->request->getData());
            if ( $this->Queries->save( $query ) ) {
                $this->Flash->success( __( 'The query has been saved.' ) );

                return $this->redirect( [ 'action' => 'view', $id ] );
            } else {
                $this->Flash->error( __( 'The query could not be saved. Please, try again.' ) );
            }
        }

        $markProperties = $this->getTableLocator()->get( 'MarkFormProperties' );
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

        $this->QueryGroups = $this->fetchTable( 'QueryGroups' );
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
}
