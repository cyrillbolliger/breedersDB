<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Exception\Exception;
use Cake\Event\Event;

/**
 * Extra layer to read mark values with all necessary information
 */
class MarksReaderComponent extends Component {
	/**
	 * Hold the Marks model
	 */
	protected $Marks;

	/**
	 * Hold the MarkValues model
	 */
	protected $MarkValues;

	/**
	 * Hold the MarkFormProperties model
	 */
	protected $MarkFormProperties;

	/**
	 * Hold the MarkFormPropertyTypes model
	 */
	protected $MarkFormPropertyTypes;

	/**
	 * Is called after the controller’s beforeFilter method but before the
	 * controller executes the current action handler.
	 *
	 * @param \Cake\Event\Event $event
	 */
	public function startup( \Cake\Event\EventInterface $event ) {
        $tableLocator = \Cake\Datasource\FactoryLocator::get('Table');

		$this->Marks                 = $tableLocator->get( 'Marks' );
		$this->MarkValues            = $tableLocator->get( 'MarkValues' );
		$this->MarkFormProperties    = $tableLocator->get( 'MarkFormProperties' );
		$this->MarkFormPropertyTypes = $tableLocator->get( 'MarkFormPropertyTypes' );
	}

	/**
	 * Return mark values of given tree and/or variety and/or batch, sorted by mark form property type
	 *
	 * @param int|array $treeId
	 * @param int $varietyId
	 * @param int $batchId
	 * @param int $markId
	 *
	 * @return array
	 * @throws Exception
	 */
	public function get( $treeId = null, int $varietyId = null, int $batchId = null, int $markId = null ) {
		if ( null === $treeId && null === $varietyId && null === $batchId ) {
			throw new \Exception( __( 'Invalid arguments.' ) );
		}

		$MarkFormPropertyTypes = $this->MarkFormPropertyTypes->find( 'list' );

		$data = array();

		foreach ( $MarkFormPropertyTypes as $key => $value ) {
			$data[ $value ] = $this->_findByType( $key, (array) $treeId, $varietyId, $batchId, $markId );
		}

		return $data;
	}

	/**
	 * Return query to find marks, its values and properties filtered by given type, tree or variety or batch
	 *
	 * @param int $typeId
	 * @param array|null $treeId
	 * @param int|null $varietyId
	 * @param int|null $batchId
	 * @param int|null $markId
	 *
	 * @return \Cake\ORM\Query
	 */
	protected function _findByType( int $typeId, $treeId, $varietyId, $batchId, $markId ) {
		$where = array();
		if ( $treeId ) {
			$where[] = [ 'Marks.tree_id IN' => $treeId ];
		}

		if ( $varietyId ) {
			$where[] = [ 'Marks.variety_id' => $varietyId ];
		}

		if ( $batchId ) {
			$where[] = [ 'Marks.batch_id' => $batchId ];
		}

		$query = $this->MarkValues->find()
		                          ->contain( [
			                          'Marks',
			                          'MarkFormProperties',
			                          'Marks.Trees',
			                          'Marks.Varieties',
			                          'Marks.Varieties.Batches',
			                          'Marks.Batches',
		                          ] )
		                          ->where( [
			                          'MarkFormProperties.mark_form_property_type_id' => $typeId,
			                          'OR'                                            => $where,
		                          ] )
		                          ->order( [
			                          'Marks.date' => 'DESC',
		                          ] );

		if ( $markId ) {
			$query->andWhere( [ 'MarkValues.mark_id' => $markId ] );
		}

		return $query;
	}

}
