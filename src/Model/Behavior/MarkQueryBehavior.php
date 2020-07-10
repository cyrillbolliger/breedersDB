<?php
/**
 * Created by PhpStorm.
 * User: cyrillbolliger
 * Date: 15.10.17
 * Time: 23:03
 */

namespace App\Model\Behavior;

use App\Model\Entity\AggregatedMark;
use Cake\Collection\Collection;
use Cake\Collection\CollectionInterface;
use Cake\ORM\Behavior;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

class MarkQueryBehavior extends Behavior {
	/**
	 * @var string which holds the way we want to retrieve the breeding object (and which breeding objects).
	 * Possible values:
	 * - 'trees': get marks of trees only, group by tree
	 * - 'varieties': get marks of varieties only, group by variety
	 * - 'convar': get marks of trees AND varieties, group by convar
	 * - 'batches': get marks of batches only, group by batches
	 */
	private $mode;

	/**
	 * @var array of mark properties we want to use in the query
	 */
	private $markProperties;

	/**
	 * @var array with used fields in dot notation
	 */
	private $fields;

	/**
	 * @var array with the filter conditions for all fields but the mark fields
	 */
	private $regularFieldsFilter;

	/**
	 * @var array with the filter conditions for the mark fields
	 */
	private $markFieldFilter;

	/**
	 * @var array @see self::mode
	 */
	private $allowedModes = [
		'trees',
		'varieties',
		'convar',
		'batches',
	];

	/**
	 * Custom finder to find data with the marks view as root table.
	 *
	 * This finder does return a collection instead of a query object, since
	 * it uses highly complex map reduce jobs for filtering and intermediate
	 * calculations.
	 *
	 * The regular fields filter is a classical orm where argument. The mark
	 * field filter however must be an array containing MarkFilter objects.
	 * While the regular fields filter conditions are applied directly
	 * when querying the database, the mark field filter are applied only
	 * after preprocessing the results, since they depend on calculations
	 * made during preprocessing. Therefore they can't be nested with regular
	 * field filters. Mark field filters are always applied with a 'and'
	 * conjunction (between each other, but also between them and the regular
	 * field filters).
	 *
	 * @param string $mode allowed values: 'trees', 'varieties', 'convar', 'batches'
	 * @param array $display fields to display - in dot notation
	 * @param array $markProperties the ids of the mark properties to display or filter
	 * @param array $regularFieldsFilter the where conditions for the display fields
	 * @param array $markFieldFilter the where conditions for the marks (MarkFilter)
	 *
	 * @return CollectionInterface
	 *
	 * @throws \Exception if the given mode is not defined
	 */
	public function customFindMarks(
		string $mode,
		array $display,
		array $markProperties,
		array $regularFieldsFilter,
		array $markFieldFilter
	): CollectionInterface {
		if ( ! in_array( $mode, $this->allowedModes ) ) {
			throw new \Exception( "The mode '{$mode}' is not defined.'" );
		}

		$this->mode           = $mode;
		$this->markProperties = $markProperties;
		$this->fields         = $display;

		$this->regularFieldsFilter = $regularFieldsFilter;
		$this->markFieldFilter     = $markFieldFilter;

		$data = $this->_getData();

		return $data;
	}

	/**
	 * Return breeding objects according to $this->mode ('convar' will return varieties)
	 * containing the marks specified in $this->properties and the fields specified
	 * in $this->display, all filtered by $this->regularFieldsFilter.
	 *
	 * @return CollectionInterface
	 *
	 * @throws \Exception if the given mode is not defined.
	 */
	private function _getData(): CollectionInterface {
		$query = $this->_getQuery();

		$groupedByMark         = $this->_groupByMark( $query );
		$aggregated            = $this->_aggregate( $groupedByMark );
		$groupedByObj          = $this->_groupByBreedingObject( $aggregated );
		$filteredByMarkValues  = $this->_filterByMarkValues( $groupedByObj );
		$markedBreedingObjects = $this->_loadAssociatedObjects( $filteredByMarkValues );

		return $markedBreedingObjects;
	}

	/**
	 * Set query according to $this->mode. Only extract fields defined in $this->display.
	 * Set where clause as given in $this->regularFieldsFilter and select only marks
	 * with properties as in $this->markProperties.
	 *
	 * @return Query
	 *
	 * @throws \Exception if the given mode is not defined.
	 */
	private function _getQuery(): Query {
		$marks = TableRegistry::getTableLocator()->get( 'MarksView' );

		$associations = null;
		switch ( $this->mode ) {
			case 'trees':
				$associations             = 'TreesView';
				$breedingObjectConditions = [ 'NOT' => [ 'MarksView.tree_id IS NULL' ] ];
				break;
			case 'varieties':
				$associations             = 'VarietiesView';
				$breedingObjectConditions = [ 'NOT' => [ 'MarksView.variety_id IS NULL' ] ];
				break;
			case 'batches':
				$associations             = 'BatchesView';
				$breedingObjectConditions = [ 'NOT' => [ 'MarksView.batch_id IS NULL' ] ];
				break;
			case 'convar':
				$associations             = [ 'TreesView', 'VarietiesView' ];
				$breedingObjectConditions = [
					'AND' => [
						'NOT' => [
							[ 'MarksView.tree_id IS NULL' ],
							[ 'MarksView.variety_id IS NULL' ]
						]
					]
				];
				break;
			default:
				throw new \Exception( "The mode '{$this->mode}' is not defined." );
		}

		return $marks->find()
		             ->select( $this->_getInterallyNeededFields() )
		             ->contain( $associations )
		             ->where( $this->regularFieldsFilter )
		             ->andWhere( [ 'MarksView.property_id IN' => $this->markProperties ] )
		             ->andWhere( $breedingObjectConditions );
	}

	/**
	 * Return array with all fields that are used internally
	 *
	 * @return array
	 * @throws \Exception
	 */
	private function _getInterallyNeededFields(): array {
		$markFields = [
			'MarksView.id',
			'MarksView.value',
			'MarksView.property_id',
			'MarksView.field_type',
		];

		switch ( $this->mode ) {
			case 'trees':
				$obj_fields = [
					'MarksView.tree_id',
				];
				break;

			case 'varieties':
				$obj_fields = [
					'MarksView.variety_id',
				];
				break;

			case 'batches':
				$obj_fields = [
					'MarksView.batch_id',
				];
				break;

			case 'convar':
				$obj_fields = [
					'MarksView.tree_id',
					'MarksView.variety_id',
					'TreesView.variety_id',
				];
				break;

			default:
				throw new \Exception( "The mode '{$this->mode}' is not defined." );
		}

		return array_merge( $markFields, $obj_fields );
	}

	/**
	 * Return collection grouped by mark and breeders object
	 *
	 * @param Query $marks
	 *
	 * @return CollectionInterface
	 *
	 * @throws \Exception if the given mode is not defined.
	 */
	private function _groupByMark( Query $marks ): CollectionInterface {
		// group by mark AND breeders object otherwise we'll aggregate all objects
		return $marks->groupBy( function ( $mark ) {
			switch ( $this->mode ) {
				case 'trees':
					return $mark->tree_id . '_' . $mark->property_id;

				case 'varieties':
					return $mark->variety_id . '_' . $mark->property_id;

				case 'batches':
					return $mark->batch_id . '_' . $mark->property_id;

				case 'convar':
					$variety_id = null === $mark->variety_id ? $mark->trees_view->variety_id : $mark->variety_id;

					return $variety_id . '_' . $mark->property_id;

				default:
					throw new \Exception( "The mode '{$this->mode}' is not defined." );
			}
		} );
	}

	/**
	 * Reduce marks into one mark element containing the aggregated values in the field value
	 * and the single values with their reference in the field values. Also add a sort_value.
	 * @see MarksAggregatorUtility::aggregate() for more details.
	 *
	 * @param CollectionInterface $groupedMarks
	 *
	 * @return CollectionInterface
	 */
	private function _aggregate( CollectionInterface $groupedMarks ): CollectionInterface {
		return $groupedMarks->map( function ( $marks ) {
			$property_id = $marks[0]['property_id'];
			$sort_by     = $this->markFieldFilter[ $property_id ]->mode;

			$collection = new Collection( $marks );
			$aggregator = new AggregatedMark( $this->mode, $sort_by );

			return $aggregator->aggregate( $collection );
		} );
	}

	/**
	 * Return aggregated marks grouped by breeders object
	 *
	 * @param CollectionInterface $marks
	 *
	 * @return CollectionInterface
	 *
	 * @throws \Exception if the given mode is not defined.
	 */
	private function _groupByBreedingObject( CollectionInterface $marks ): CollectionInterface {
		return $marks->groupBy( function ( $mark ) {
			// aggregate by breeders obj
			return $mark->parent_id;
		} )->map( function ( $breeding_obj ) {
			// make sure the marks have their property id as keys
			return ( new Collection( $breeding_obj ) )->indexBy( function ( $mark ) {
				return $mark->property_id;
			} );
		} );
	}

	/**
	 * Return collection filtered by mark values using MarkQueryBehavior::markFieldFilter
	 *
	 * @param CollectionInterface $markedObj
	 *
	 * @return CollectionInterface
	 */
	private function _filterByMarkValues( CollectionInterface $markedObj ): CollectionInterface {
		return $markedObj->filter( function ( $item ) {
			foreach ( $this->markFieldFilter as $property_id => $filter ) {
				// if no filter is set
				if ( $filter->isEmpty() ) {
					continue;
				}

				// if mark is not present
				if ( ! isset( $item->toArray()[ $property_id ] ) ) {
					return false;
				}

				// if we don't meet the filter test
				if ( ! $filter->test( $item->toArray()[ $property_id ] ) ) {
					return false;
				}
			}

			return true;
		} );
	}

	/**
	 * Return collection of filtered breeders objects with aggregated and filtered marks
	 *
	 * @param CollectionInterface $items
	 *
	 * @return CollectionInterface
	 *
	 * @throws \Exception if the given mode is not defined.
	 */
	private function _loadAssociatedObjects( CollectionInterface $items ): CollectionInterface {
		$table = $this->_getTable();

		return $items->map( function ( $item ) use ( $table ) {
			$entity        = $table->get( $item->first()->parent_id, [
				'select' => $this->fields
			] );
			$entity->marks = $item;

			return $entity;
		} );
	}

	/**
	 * Return the table object that corresponds to the current mode
	 *
	 * @throws \Exception if the given mode is not defined.
	 */
	private function _getTable(): Table {
		switch ( $this->mode ) {
			case 'trees':
				$table = 'TreesView';
				break;

			case 'convar':
			case 'varieties':
				$table = 'VarietiesView';
				break;

			case 'batches':
				$table = 'BatchesView';
				break;

			default:
				throw new \Exception( "The mode '{$this->mode}' is not defined." );
		}

		return TableRegistry::getTableLocator()->get( $table );
	}

	/**
	 * Callback function for Collection::sortBy() used
	 * in the CollectionPaginatorComponent to sort mark
	 * view results.
	 *
	 * @param string sort the field to sort by
	 *
	 * @return string|callable that takes the object
	 * to sort and returns the value to sort by or
	 * the dot noted field name to sort by as string.
	 */
	public function sort( string $sort ) {
		// if we we sort by a regular field, just return the field name
		if ( false === strpos( $sort, 'mark-' ) ) {
			return $sort;
		}

		// if we want to sort by a mark value, return callback
		$mark_id = str_replace( 'mark-', '', $sort );

		return function ( $obj ) use ( $mark_id ) {

			// if the mark is missing return "" to indicate that it is smaller
			if ( ! isset( $obj->marks->toArray()[ $mark_id ] ) ) {
				return "";
			}


			return $obj->marks->toArray()[ $mark_id ]->sort_value;
		};
	}
}
