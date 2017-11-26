<?php
/**
 * Created by PhpStorm.
 * User: cyrillbolliger
 * Date: 15.10.17
 * Time: 23:03
 */

namespace App\Model\Behavior;

use App\Model\Entity\AggregatedMark;
use Cake\Cache\Cache;
use Cake\Collection\Collection;
use Cake\Collection\CollectionInterface;
use Cake\Collection\Iterator\ReplaceIterator;
use Cake\ORM\Behavior;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

class MarkQueryBehavior extends Behavior {
	/**
	 * @var bool clean query cache
	 */
	private $clearCache;
	
	/**
	 * @var array with the field as key and the direction as value
	 */
	private $orderBy;
	
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
	 * @var array @see $this->mode
	 */
	private $allowedModes = [
		'trees',
		'varieties',
		'convar',
		'batches',
	];
	
	/**
	 * ToDo: proper description
	 *
	 * @param string $mode allowed values: 'trees', 'varieties', 'convar', 'batches'
	 * @param array $display fields to display - in dot notation
	 * @param array $markProperties the ids of the mark properties to display or filter
	 * @param array $regularFieldsFilter the where conditions for the display fields
	 * @param array $markFieldFilter the where conditions for the marks
	 * @param bool $clearCache set to true to rebuild the cache
	 * @param array $orderBy with the field key as key and the direction as value
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
		array $markFieldFilter,
		bool $clearCache,
		array $orderBy
	): CollectionInterface {
		if ( ! in_array( $mode, $this->allowedModes ) ) {
			throw new \Exception( "The mode '{$mode}' is not defined.'" );
		}
		
		$this->mode           = $mode;
		$this->clearCache     = $clearCache;
		$this->orderBy        = $orderBy;
		$this->markProperties = $markProperties;
		$this->fields         = $display;
		
		$this->regularFieldsFilter = $regularFieldsFilter;
		$this->markFieldFilter     = $markFieldFilter;
		
		$data   = $this->_getData();
		$sorted = $this->_sort( $data );
		
		return $sorted;
	}
	
	/**
	 * Return breeding objects according to $this->mode ('convar' will return varieties)
	 * containing the marks specified in $this->properties and the fields specified
	 * in $this->display, all filtered by $this->regularFieldsFilter. If a valid cache exists and
	 * $this->clearCache is set to false, the intermediate results will be served from
	 * cache. The cache is mainly used to speed up sorting and browsing using the paginator.
	 *
	 * @return CollectionInterface
	 */
	private function _getData(): CollectionInterface {
		if ( $this->clearCache ) {
			$this->_clearCache();
		}
		
		/*$cached = $this->_getCachedResults();
		if (false !== $cached) {
			return $cached; // todo: fix it
		}*/
		
		$query = $this->_getQuery();
		
		$filtered              = $this->_preFilterResults( $query );
		$groupedByMark         = $this->_groupByMark( $filtered );
		$aggregated            = $this->_aggregate( $groupedByMark );
		$groupedByObj          = $this->_groupByBreedingObject( $aggregated );
		$filteredByMarkValues  = $this->_filterByMarkValues( $groupedByObj );
		$markedBreedingObjects = $this->_loadAssociatedObjects( $filteredByMarkValues );
		
		$this->_cacheResults( $markedBreedingObjects );
		
		return $markedBreedingObjects;
	}
	
	/**
	 * Delete cached query
	 *
	 * @return CollectionInterface|boolean false if no cache exists
	 */
	private function _clearCache() {
		return Cache::delete( $this->_getCacheKey() );
	}
	
	/**
	 * Create a hash over the changing input fields to make sure we only use the cache when it hasn't changed.
	 *
	 * @return string
	 */
	private function _getCacheKey(): string {
		return 'markQuery_' . md5(
				json_encode( $this->mode ) .
				json_encode( $this->fields ) .
				json_encode( $this->markProperties ) .
				json_encode( $this->regularFieldsFilter ) .
				json_encode( $this->markFieldFilter )
			);
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
		$marks = TableRegistry::get( 'MarksView' );
		
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
	 * Remove unused mark properties and entities without marks
	 *
	 * @param Query $query
	 *
	 * @return CollectionInterface with the filtered data
	 */
	private function _preFilterResults( Query $query ): CollectionInterface {
		return $query->filter( function ( $item ) {
			
			// todo: check if this mathod can be removed
			
			return true;
		} );
	}
	
	/**
	 * Return collection grouped by mark and breeders object
	 *
	 * @param CollectionInterface $marks
	 *
	 * @return CollectionInterface
	 *
	 * @throws \Exception if the given mode is not defined.
	 */
	private function _groupByMark( CollectionInterface $marks ): CollectionInterface {
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
	 * and the single values with their reference in the field values.
	 * @see MarksAggregatorUtility::aggregate() for more details.
	 *
	 * @param CollectionInterface $groupedMarks
	 *
	 * @return CollectionInterface
	 */
	private function _aggregate( CollectionInterface $groupedMarks ): CollectionInterface {
		return $groupedMarks->map( function ( $marks ) {
			$collection = new Collection( $marks );
			$aggregator = new AggregatedMark( $this->mode );
			
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
		
		return TableRegistry::get( $table );
	}
	
	/**
	 * Cache results
	 *
	 * @param ReplaceIterator $obj
	 */
	private function _cacheResults( ReplaceIterator $obj ) {
		Cache::write( $this->_getCacheKey(), $obj );
	}
	
	/**
	 * Get cached results
	 *
	 * @return ReplaceIterator|false
	 */
	private function _getCachedResults() {
		return Cache::read($this->_getCacheKey());
	}
	
	/**
	 * Return data sorted according to $this->sort
	 *
	 * @param CollectionInterface $data
	 *
	 * @return CollectionInterface
	 */
	private function _sort( CollectionInterface $data ) {
		// todo
		return $data;
	}
}