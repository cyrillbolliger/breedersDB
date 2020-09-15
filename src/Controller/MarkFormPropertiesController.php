<?php

namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Exception\Exception;

/**
 * MarkFormProperties Controller
 *
 * @property \App\Model\Table\MarkFormPropertiesTable $MarkFormProperties
 */
class MarkFormPropertiesController extends AppController {
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
		$this->paginate['contain'] = [ 'MarkFormPropertyTypes' ];

		$markFormProperties = $this->paginate( $this->MarkFormProperties );

		$this->set( compact( 'markFormProperties' ) );
		$this->set( '_serialize', [ 'markFormProperties' ] );
	}

	/**
	 * View method
	 *
	 * @param string|null $id Mark Form Property id.
	 *
	 * @return \Cake\Network\Response|null
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function view( $id = null ) {
		$markFormProperty = $this->MarkFormProperties->get( $id, [
			'contain' => [ 'MarkFormPropertyTypes', 'MarkFormFields', 'MarkFormFields.MarkForms' ]
		] );

		$this->set( 'markFormProperty', $markFormProperty );
		$this->set( '_serialize', [ 'markFormProperty' ] );
	}

	/**
	 * Add method
	 *
	 * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$markFormProperty = $this->MarkFormProperties->newEntity();
		if ( $this->request->is( 'post' ) ) {
			$markFormProperty = $this->MarkFormProperties->patchEntity( $markFormProperty, $this->request->getData());
			if ( $this->MarkFormProperties->save( $markFormProperty ) ) {
				$this->Flash->success( __( 'The mark form property has been saved.' ) );

				return $this->redirect( [ 'action' => 'index' ] );
			} else {
				$this->Flash->error( __( 'The mark form property could not be saved. Please, try again.' ) );
			}
		}
		$fieldTypes            = $this->MarkFormProperties->getFieldTypes();
		$markFormPropertyTypes = $this->MarkFormProperties->MarkFormPropertyTypes->find( 'list' );
		$this->set( compact( 'markFormProperty', 'markFormPropertyTypes', 'fieldTypes' ) );
		$this->set( '_serialize', [ 'markFormProperty' ] );
	}

	/**
	 * Edit method
	 *
	 * @param string|null $id Mark Form Property id.
	 *
	 * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Network\Exception\NotFoundException When record not found.
	 */
	public function edit( $id = null ) {
		$markFormProperty = $this->MarkFormProperties->get( $id, [
			'contain' => []
		] );
		if ( $this->request->is( [ 'patch', 'post', 'put' ] ) ) {
			$markFormProperty = $this->MarkFormProperties->patchEntity( $markFormProperty, $this->request->getData());
			if ( $this->MarkFormProperties->save( $markFormProperty ) ) {
				$this->Flash->success( __( 'The mark form property has been saved.' ) );

				return $this->redirect( [ 'action' => 'index' ] );
			} else {
				$this->Flash->error( __( 'The mark form property could not be saved. Please, try again.' ) );
			}
		}
		$fieldTypes            = $this->MarkFormProperties->getFieldTypes();
		$markFormPropertyTypes = $this->MarkFormProperties->MarkFormPropertyTypes->find( 'list' );
		$this->set( compact( 'markFormProperty', 'markFormPropertyTypes', 'fieldTypes' ) );
		$this->set( '_serialize', [ 'markFormProperty' ] );
	}

	/**
	 * Delete method
	 *
	 * @param string|null $id Mark Form Property id.
	 *
	 * @return \Cake\Network\Response|null Redirects to index.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function delete( $id = null ) {
		$this->request->allowMethod( [ 'post', 'delete' ] );
		$markFormProperty = $this->MarkFormProperties->get( $id );
		if ( $this->MarkFormProperties->delete( $markFormProperty ) ) {
			$this->Flash->success( __( 'The mark form property has been deleted.' ) );
		} else {
			$this->Flash->error( __( 'The mark form property could not be deleted. Please, try again.' ) );
		}

		return $this->redirect( [ 'action' => 'index' ] );
	}

	/**
	 * Get form field (property)
	 *
	 * @param string|null $id Mark Form Property id.
	 * @param string|null $view view we want to use to return the value.
	 *
	 * @return \Cake\Network\Response|null
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function get( $id = null, $view = null ) {
		$markFormProperty = $this->MarkFormProperties->get( $id, [
			'contain' => [ 'MarkFormPropertyTypes', 'MarkFormFields', 'MarkValues' ]
		] );

		$this->set( 'markFormProperty', $markFormProperty );
		$this->set( '_serialize', [ 'markFormProperty' ] );

		switch ( $view ) {
			case 'field_edit_form_mode':
				$this->render( '/Element/Mark/field_edit_form_mode' );
				break;

			default:
				$this->render( '/Element/Mark/field' );
				break;
		}
	}

	/**
	 * Return filtered index table
	 */
	public function filter() {
		$allowed_fields = [ 'name' ];

		if ( $this->request->is( 'get' )
		     && $this->request->is( 'ajax' )
		     && ! empty( $this->request->getQuery('fields') )
		     && array_intersect( $allowed_fields, $this->request->getQuery('fields') )
		) {
			$entries = $this->MarkFormProperties->filter( $this->request->getQuery('term') );

            $this->Filter->setSortingParams();
            $this->Filter->setPaginationParams($entries);

		} else {
			throw new Exception( __( 'Direct access not allowed.' ) );
		}

		if ( $entries ) {
			$markFormProperties = $this->paginate( $entries );
			$this->set( compact( 'markFormProperties' ) );
			$this->set( '_serialize', [ 'markFormProperties' ] );
			$this->render( '/Element/MarkFormProperty/index_table' );
		} else {
			$this->render( '/Element/nothing_found' );
		}
	}
}
