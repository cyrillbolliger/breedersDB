<?php

namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Exception\Exception;

/**
 * MarkScannerCodes Controller
 *
 * @property \App\Model\Table\MarkScannerCodesTable $MarkScannerCodes
 */
class MarkScannerCodesController extends AppController {

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
		$this->paginate   = [
			'contain' => [ 'MarkFormProperties' ],
			'limit' => 100,
		];
		$markScannerCodes = $this->paginate( $this->MarkScannerCodes );
		$properties       = $this->MarkScannerCodes->MarkFormProperties
			->find( 'list' )
			->order( [ 'name' => 'asc' ] )
			->toArray();

		$all                  = [ 0 => '(all)']; // dont translate, because of bug in cakephp
		$mark_form_properties = $all + $properties;

		$this->set( compact( 'markScannerCodes', 'mark_form_properties' ) );
		$this->set( '_serialize', [ 'markScannerCodes' ] );
	}

	/**
	 * View method
	 *
	 * @param string|null $id Mark Scanner Code id.
	 *
	 * @return \Cake\Network\Response|null
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function view( $id = null ) {
		$markScannerCode = $this->MarkScannerCodes->get( $id, [
			'contain' => [ 'MarkFormProperties' ]
		] );

		$this->set( 'markScannerCode', $markScannerCode );
		$this->set( '_serialize', [ 'markScannerCode' ] );
	}

	/**
	 * Add method
	 *
	 * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$markScannerCode = $this->MarkScannerCodes->newEmptyEntity();
		$data            = $this->request->getData();
		$data['code']    = $this->MarkScannerCodes->getNextFreeCode();
		if ( $this->request->is( 'post' ) ) {
			$markScannerCode = $this->MarkScannerCodes->patchEntity( $markScannerCode, $data );
			if ( $this->MarkScannerCodes->save( $markScannerCode ) ) {
				$property = $this->MarkScannerCodes->MarkFormProperties->get( $markScannerCode->mark_form_property_id );
				$this->Flash->success(
					__(
						'{0}: {1} has been saved.',
						'<strong>' . h( $property->name ) . '</strong>',
						'<strong>' . h( $markScannerCode->mark_value ) . '</strong>'
					),
					[ 'escape' => false ]
				);

				return $this->redirect( [ 'action' => 'print', $markScannerCode->id, 'add' ] );
			} else {
				$this->Flash->error( __( 'The mark scanner code could not be saved. Please, try again.' ) );
			}
		}
		$markFormProperties = $this->MarkScannerCodes->MarkFormProperties->find( 'list' );
		$this->set( compact( 'markScannerCode', 'markFormProperties' ) );
		$this->set( '_serialize', [ 'markScannerCode' ] );
	}

	/**
	 * Edit method
	 *
	 * @param string|null $id Mark Scanner Code id.
	 *
	 * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Network\Exception\NotFoundException When record not found.
	 */
	public function edit( $id = null ) {
		$markScannerCode = $this->MarkScannerCodes->get( $id, [
			'contain' => [ 'MarkFormProperties' ]
		] );
		if ( $this->request->is( [ 'patch', 'post', 'put' ] ) ) {
			$markScannerCode = $this->MarkScannerCodes->patchEntity( $markScannerCode, $this->request->getData());
			if ( $this->MarkScannerCodes->save( $markScannerCode ) ) {
				$this->Flash->success( __( 'The mark scanner code has been saved.' ) );

				return $this->redirect( [ 'action' => 'index' ] );
			} else {
				$this->Flash->error( __( 'The mark scanner code could not be saved. Please, try again.' ) );
			}
		}
		$markFormProperties = $this->MarkScannerCodes->MarkFormProperties->find( 'list' );
		$this->set( compact( 'markScannerCode', 'markFormProperties' ) );
		$this->set( '_serialize', [ 'markScannerCode' ] );
	}

	/**
	 * Delete method
	 *
	 * @param string|null $id Mark Scanner Code id.
	 *
	 * @return \Cake\Network\Response|null Redirects to index.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function delete( $id = null ) {
		$this->request->allowMethod( [ 'post', 'delete' ] );
		$markScannerCode = $this->MarkScannerCodes->get( $id );
		if ( $this->MarkScannerCodes->delete( $markScannerCode ) ) {
			$this->Flash->success( __( 'The mark scanner code has been deleted.' ) );
		} else {
			$this->Flash->error( __( 'The mark scanner code could not be deleted. Please, try again.' ) );
		}

		return $this->redirect( [ 'action' => 'index' ] );
	}

	/**
	 * Return filtered index table
	 */
	public function filter() {
		$allowed_fields = [ 'mark_form_property_id' ];

		if ( $this->request->is( 'get' )
		     && $this->request->is( 'ajax' )
		     && ! empty( $this->request->getQuery('fields') )
		     && array_intersect( $allowed_fields, $this->request->getQuery('fields') )
		) {
			$entries = $this->MarkScannerCodes->filter( $this->request->getQuery('term') );
		} else {
			throw new \Exception( __( 'Direct access not allowed.' ) );
		}

		if ( $entries && $entries->count() ) {
            $this->Filter->setSortingParams();
            $this->Filter->setPaginationParams($entries);
			$markScannerCodes = $this->paginate( $entries );

			$this->set( compact( 'markScannerCodes' ) );
			$this->set( '_serialize', [ 'markScannerCodes' ] );
			$this->render( '/element/MarkScannerCode/index_table' );
		} else {
			$this->render( '/element/nothing_found' );
		}
	}

	public function getMark() {
		if ( $this->request->is( 'get' ) && $this->request->is( 'ajax' ) ) {
			$entries = $this->MarkScannerCodes->find()
			                                  ->select( [ 'mark_value', 'mark_form_property_id' ] )
			                                  ->where( [ 'code' => $this->request->getQuery('term') ] )
			                                  ->first()
			                                  ->toArray();
		} else {
			throw new \Exception( __( 'Direct access not allowed.' ) );
		}

		$this->set( [ 'data' => $entries ] );
		$this->render( '/element/ajaxreturn' );
	}

	/**
	 * Show the print dialog
	 *
	 * @param int|string $id
	 * @param string $caller action to redirect after printing
	 * @param mixed $params for action
	 */
	public function print( $id, string $caller, $params = null ) {
		$zpl = $this->MarkScannerCodes->getLabelZpl( (int) $id );

		$this->set( [
			'buttons'    => [ 'regular' => [ 'label' => __( 'Regular' ), 'zpl' => $zpl ] ],
			'focus'      => 'regular',
			'controller' => 'MarkScannerCodes',
			'action'     => $caller,
			'params'     => $params,
			'nav'        => 'Mark/nav'
		] );
		$this->render( '/element/print' );
	}

	/**
	 * Show the print dialog
	 *
	 * @param int $id
	 * @param string $caller action to redirect after printing
	 * @param mixed $params for action
	 */
	public function printSubmit() {
		$zpl = $this->MarkScannerCodes->getSubmitLabelZpl();

		$this->set( [
			'buttons'    => [ 'regular' => [ 'label' => __( 'Regular' ), 'zpl' => $zpl ] ],
			'focus'      => 'regular',
			'controller' => 'MarkScannerCodes',
			'action'     => 'index',
			'params'     => null,
			'nav'        => 'Mark/nav'
		] );
		$this->render( '/element/print' );
	}
}
