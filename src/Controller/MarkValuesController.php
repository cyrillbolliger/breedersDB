<?php

namespace App\Controller;

use App\Controller\AppController;

/**
 * MarkValues Controller
 *
 * @property \App\Model\Table\MarkValuesTable $MarkValues
 */
class MarkValuesController extends AppController {
	/**
	 * Edit method
	 *
	 * @param string|null $id Mark Value id.
	 *
	 * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Network\Exception\NotFoundException When record not found.
	 */
	public function edit( $id = null ) {
		$markValue = $this->MarkValues->get( $id, [
			'contain' => [ 'MarkFormProperties' ]
		] );
		if ( $this->request->is( [ 'patch', 'post', 'put' ] ) ) {
			$data = $this->_prepareValues( $markValue );
			$markValue = $this->MarkValues->patchEntity( $markValue, $data );
			if ( $this->MarkValues->save( $markValue ) ) {
				$this->Flash->success( __( 'The mark value has been saved.' ) );

				return $this->redirect( [ 'controller' => 'Marks', 'action' => 'index' ] );
			} else {
				$this->Flash->error( __( 'The mark value could not be saved. Please, try again.' ) );
			}
		}
		$this->set( compact( 'markValue' ) );
		$this->set( '_serialize', [ 'markValue' ] );
	}

	/**
	 * Put data in the correct format
     *
     * @return array
	 */
	protected function _prepareValues( $markValue ) {
	    $data = $this->request->getData();
		if ( isset( $data['mark_form_fields']['mark_form_properties'] ) ) {

			$key = array_keys( $data['mark_form_fields']['mark_form_properties'] )[0];

			$value = $data['mark_form_fields']['mark_form_properties'][ $key ]['mark_values']['value'];
			unset( $data['mark_form_fields'] );

			$data['value']                 = $value;
			$data['mark_form_property_id'] = $markValue->mark_form_property_id;
		}

		return $data;
	}

	/**
	 * Delete method
	 *
	 * @param string|null $id Mark Value id.
	 *
	 * @return \Cake\Network\Response|null Redirects to index.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function delete( $id = null ) {
		$this->request->allowMethod( [ 'post', 'delete' ] );
		$markValue = $this->MarkValues->get( $id );
		if ( $this->MarkValues->delete( $markValue ) ) {
			$this->Flash->success( __( 'The mark value has been deleted.' ) );
		} else {
			$this->Flash->error( __( 'The mark value could not be deleted. Please, try again.' ) );
		}

		return $this->redirect( $this->referer() );
	}
}
