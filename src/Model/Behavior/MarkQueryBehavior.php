<?php
/**
 * Created by PhpStorm.
 * User: cyrillbolliger
 * Date: 15.10.17
 * Time: 23:03
 */

namespace App\Model\Behavior;

use App\Model\Entity\MarksView;
use App\Utility\MarksAggregatorUtility;
use Cake\Cache\Cache;
use Cake\Collection\Collection;
use Cake\Collection\CollectionInterface;
use Cake\Core\Exception\Exception;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Cake\ORM\Query;
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
	 * @param array $display fields to display in dot notation
	 * @param array $markProperties the name of the mark properties to display or filter
	 * @param array $regularFieldsFilter the where conditions for the display fields
	 * @param array $markFieldFilter the where conditions for the marks
	 * @param bool $clearCache set to true to rebuild the cache
	 * @param array $orderBy with the field key as key and the direction as value
	 *
	 * @return CollectionInterface
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
			throw new Exception( "'{$mode}' mode is not defined.'" );
		}
		
		$this->mode           = $mode;
		$this->clearCache     = $clearCache;
		$this->orderBy        = $orderBy;
		$this->markProperties = $markProperties;
		
		$this->_setFields( $display );
		
		$this->regularFieldsFilter = $regularFieldsFilter;
		$this->markFieldFilter     = $markFieldFilter;
		
		$data   = $this->_getData();
		$sorted = $this->_sort( $data );
		
		return $sorted;
	}
	
	/**
	 * Merge internally used field into the fields used to to display results and store it in $this->fields
	 *
	 * @param array $display fields to display in dot notation
	 */
	private function _setFields( array $display ) {
		$markFields = [
			'MarksView.value',
			'MarksView.name',
			'MarksView.field_type',
			'MarksView.date',
			'MarksView.author',
			'MarksView.exceptional_mark',
		];
		
		switch ( $this->mode ) {
			case 'trees':
				$obj_fields = [
					'MarksView.tree_id',
					'TreesView.publicid',
				];
				break;
			
			case 'varieties':
				$obj_fields = [
					'MarksView.variety_id',
					'VarietiesView.convar',
				];
				break;
			
			case 'batches':
				$obj_fields = [
					'MarksView.batch_id',
					'BatchesView.crossing_batch'
				];
				break;
			
			case 'convar':
				$obj_fields = [
					'MarksView.tree_id',
					'MarksView.variety_id',
					'TreesView.publicid',
					'TreesView.variety_id',
					'TreesView.convar',
					'VarietiesView.convar',
				];
				break;
			
			default:
				throw new Exception( "'{$this->mode}' mode is not defined.'" );
		}
		
		$this->fields = array_merge( $display, $markFields, $obj_fields );
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
		
		$query = $this->_getQuery();
		$this->_cacheResults( $query );
		
		$filtered             = $this->_preFilterResults( $query );
		$groupedByMark        = $this->_groupByMark( $filtered );
		$aggregated           = $this->_aggregate( $groupedByMark );
		$groupedByObj         = $this->_groupByBreedingObject( $aggregated );
		$markedObj            = $this->_moveMarksIntoBreedingObjects( $groupedByObj );
		$filteredByMarkValues = $this->_filterByMarkValues( $markedObj );
		
		return $filteredByMarkValues;
	}
	
	/**
	 * Delete cached query
	 *
	 * @return CollectionInterface|boolean false if no cache exists
	 */
	private function _clearCache() {
		$key = 'markQuery_' . md5( $this->mode . implode( '', $this->markProperties ) . implode( '', $this->filter ) );
		
		return Cache::delete( $key );
	}
	
	/**
	 * Set query according to $this->mode. Only extract fields defined in $this->display.
	 * Set where clause as given in $this->regularFieldsFilter
	 *
	 * @return Query
	 * @throws Exception if $this->mode is not defined
	 */
	private function _getQuery(): Query {
		$marks = TableRegistry::get( 'MarksView' );
		
		$associations = null;
		switch ( $this->mode ) {
			case 'trees':
				$associations = 'TreesView';
				break;
			case 'varieties':
				$associations = 'VarietiesView';
				break;
			case 'batches':
				$associations = 'BatchesView';
				break;
			case 'convar':
				$associations = [ 'TreesView', 'VarietiesView' ];
				break;
			default:
				throw new Exception( "'{$this->mode}' is not an defined mode.'" );
		}
		
		return $marks->find()
		             ->select( $this->fields )
		             ->contain( $associations )
		             ->where( $this->regularFieldsFilter );
	}
	
	/**
	 * Cache results
	 *
	 * @param Query $query
	 */
	private function _cacheResults( Query $query ) {
		$key = 'markQuery_' . md5( $this->mode . implode( '', $this->markProperties ) . implode( '', $this->filter ) );
		$query->cache( $key );
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
			// filter by property
			if ( ! in_array( $item->name, $this->markProperties ) ) {
				return false;
			}
			
			// filter mode
			if ( ! $this->_hasItemDataForCurrentMode( $item ) ) {
				return false;
			}
			
			return true;
		} );
	}
	
	/**
	 * Check if the given item has data for the current mode ($this->>mode).
	 *
	 * @param MarksView $item
	 *
	 * @return bool
	 */
	private function _hasItemDataForCurrentMode( MarksView $item ): bool {
		switch ( $this->mode ) {
			case 'trees':
				$fields = 'tree_id';
				break;
			case 'varieties':
				$fields = 'variety_id';
				break;
			case 'batches':
				$fields = 'batch_id';
				break;
			case 'convar':
				$fields = [ 'tree_id', 'variety_id' ];
				break;
			default:
				throw new Exception( "'{$this->mode}' is not an defined mode.'" );
		}
		
		foreach ( (array) $fields as $field ) {
			if ( ! empty( $item->$field ) ) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Return collection grouped by mark and breeders object
	 *
	 * @param CollectionInterface $marks
	 *
	 * @return CollectionInterface
	 */
	private function _groupByMark( CollectionInterface $marks ): CollectionInterface {
		return $marks->groupBy( function ( $mark ) {
			switch ( $this->mode ) {
				case 'trees':
					return $mark->tree_id . $mark->name;
				
				case 'varieties':
					return $mark->variety_id . $mark->name;
				
				case 'batches':
					return $mark->batch_id . $mark->name;
				
				case 'convar':
					if ( ! empty( $mark->variety_id ) ) {
						return $mark->variety_id . $mark->name;
					}
					
					return $mark->trees_view->variety_id . $mark->name;
				
				default:
					throw new Exception( "'{$this->mode}' is not an defined mode.'" );
			}
			// group by mark AND breeders object otherwise the stats will aggregate all objects
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
			$aggregator = new MarksAggregatorUtility( $this->mode );
			
			return $aggregator->aggregate( $collection );
		} );
	}
	
	/**
	 * Return aggregated marks grouped by breeders object
	 *
	 * @param CollectionInterface $marks
	 *
	 * @return CollectionInterface
	 */
	private function _groupByBreedingObject( CollectionInterface $marks ): CollectionInterface {
		switch ( $this->mode ) {
			case 'trees':
				return $marks->groupBy( 'tree_id' );
			
			case 'varieties':
				return $marks->groupBy( 'variety_id' );
			
			case 'batches':
				return $marks->groupBy( 'batch_id' );
			
			case 'convar':
				return $marks->groupBy( function ( $mark ) {
					return empty( $mark->tree_id ) ? $mark->varieties_view->convar : $mark->trees_view->convar;
				} );
			
			default:
				throw new Exception( "'{$this->mode}' is not an defined mode.'" );
		}
	}
	
	/**
	 * Return the breeders object (ex. tree) with its marks added to the field marks (array).
	 *
	 * @param CollectionInterface $breedersObjectsMarks
	 *
	 * @return CollectionInterface
	 */
	private function _moveMarksIntoBreedingObjects( CollectionInterface $breedersObjectsMarks ): CollectionInterface {
		return $breedersObjectsMarks->map( function ( $marks ) {
			$obj = $this->_getBreedersObjectFromMarks( $marks );
			
			$obj->marks = array();
			
			foreach ( $marks as $mark ) {
				$obj->marks[ $mark->name ] = (object) [
					'name'                    => $mark->name,
					'value'                   => $mark->value,
					'values'                  => $mark->values,
					'field_type'              => $mark->field_type,
					'mark_form_property_type' => $mark->mark_form_property_type,
				];
			}
			
			return $obj;
		} );
	}
	
	/**
	 * Return breeders object (ex. tree) from given mark respecting $this->mode.
	 * If the mode 'convar' is selected the breeders object will always be the variety.
	 *
	 * @param array $marks
	 *
	 * @return Entity
	 */
	private function _getBreedersObjectFromMarks( array $marks ): Entity {
		switch ( $this->mode ) {
			case 'trees':
				return $marks[0]->trees_view;
			
			case 'varieties':
				return $marks[0]->varieties_view;
			
			case 'batches':
				return $marks[0]->batches_view;
			
			case 'convar': // always return varieties_view as breeders obj
				if ( ! empty( $marks[0]->varieties_view ) ) {
					// if its already loaded return it directly
					return $marks[0]->varieties_view;
				}
				
				// if the varieties_view is missing query it
				$varieties = TableRegistry::get( 'VarietiesView' );
				
				return $varieties->get( $marks[0]->trees_view->variety_id );
			
			default:
				throw new Exception( "'{$this->mode}' is not an defined mode.'" );
		}
	}
	
	private function _filterByMarkValues( CollectionInterface $markedObj ): CollectionInterface {
		// todo
		return $markedObj;
		
		
		return $query->filter( function ( $item ) {
			// filter by property
			if ( ! in_array( $item->name, $this->markProperties ) ) {
				return false;
			}
			
			// filter mode
			if ( ! $this->_hasItemDataForCurrentMode( $item ) ) {
				return false;
			}
			
			return true;
		} );
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
	
	/**
	 * Return array with all possible breeding object aggregation modes.
	 *
	 * @return array
	 */
	public function getBreedingObjectAggregationModes(): array {
		return [
			'trees'     => __( 'Trees' ),
			'varieties' => __( 'Varieties' ),
			'batches'   => __( 'Batches' ),
			'convar'    => __( 'Convar' ),
		];
	}
}