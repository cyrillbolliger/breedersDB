<?php

namespace App\Controller;

use App\Controller\AppController;
use Cake\Network\Exception\BadRequestException;

/**
 * MarksView Controller
 *
 * @property \App\Model\Table\MarksViewTable $MarksView
 */
class MarksViewController extends AppController {
	/**
	 * View method
	 *
	 * @param string|null $id Marks View id.
	 *
	 * @return \Cake\Network\Response|void
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 * @throws BadRequestException If accessed directly
	 */
	public function get( $id = null ) {
		if ( ! $this->request->is( 'get' ) || ! $this->request->is( 'ajax' ) ) {
			throw new BadRequestException( __( 'Direct access not allowed.' ) );
		}
		
		$marksView = $this->MarksView->get( $id, [
			'contain' => [ 'TreesView', 'VarietiesView', 'BatchesView' ]
		] );
		
		$this->set( 'marksView', $marksView );
		$this->set( '_serialize', [ 'marksView' ] );
		
		$this->render( '/Element/MarkView/tooltip' );
	}
}
