<?php
/**
 * Created by PhpStorm.
 * User: cyrillbolliger
 * Date: 29.10.17
 * Time: 08:54
 */

namespace App\Model\Entity;

use Cake\Collection\Collection;
use Cake\Collection\Iterator\ReplaceIterator;

class AggregatedMark {

	/**
	 * @var int the mark from property id we represent
	 */
	public $property_id;

	/**
	 * @var array with the not aggregated values. The keys hold the mark.id the values the mark.value.
	 */
	public $values;

	/**
	 * @var mixed the aggregated value.
	 */
	public $value;

	/**
	 * @var mixed the values used for sorting
	 */
	public $sort_value;

	/**
	 * @var int the id of the parent breeders object
	 */
	public $parent_id;

	/**
	 * @var string the breeders object aggregation mode. @see \App\Model\Behavior\MarkQueryBehavior::mode
	 */
	private $mode;

	/**
	 * @var string defining what we want to be in self::sort_value
	 */
	private $sort_by;

	/**
	 * AggregatedMark constructor.
	 *
	 * @param string $mode
	 * @param string $sort_by
	 */
	public function __construct( string $mode, string $sort_by ) {
		$this->mode    = $mode;
		$this->sort_by = $sort_by;
	}

	/**
	 * Calculate the aggregated marks of the given mark collection.
	 * Strings and dates get concatenated (separated by '; '),
	 * true is set if one or more booleans are true, multiple
	 * statistical values (count, average, min, max, median and
	 * standard deviation are calculated for numerical values.
	 *
	 * The aggregated values are accessible in the 'value' field.
	 *
	 * The 'values' field holds an array with key value pairs,
	 * where the key represents the mark.id, the value the mark.value.
	 *
	 * @param Collection $marks
	 *
	 * @return AggregatedMark
	 *
	 * @throws \Exception if the type of the first mark is unknown.
	 */
	public function aggregate( Collection $marks ): AggregatedMark {
		// preserve single values including reference id
		$this->values = $this->_extractValuesWithReference( $marks );

		// get array of values
		$values = $marks->extract( 'value' )->toArray();

		// get mark prototype
		$prototype = $marks->first();

		// set property id
		$this->property_id = $prototype->property_id;

		// set parent id
		$this->_setParent( $prototype );

		// aggregate according to the field type
		$type = $prototype->field_type;
		switch ( $type ) {
			case 'INTEGER':
			case 'FLOAT':
				$this->value      = (object) $this->_calculateStats( $values, $type );
				$this->sort_value = &$this->value->{$this->sort_by};
				break;

			case 'DATE':
            case 'PHOTO':
			case 'VARCHAR':
				$this->value      = implode( '; ', $values );
				$this->sort_value = &$this->value;
				break;

			case 'BOOLEAN':
				$sum              = array_sum( $values );
				$this->value      = (bool) $sum;
				$this->sort_value = $sum / count( $values );
				break;

			default:
				throw new \Exception( "The field type '{$type}' is not an defined." );
		}

		return $this;
	}

	/**
	 * Return array with key value pairs, where the key holds the mark.id and the value the mark.value
	 *
	 * @param Collection $marks
	 *
	 * @return ReplaceIterator
	 */
	private function _extractValuesWithReference( Collection $marks ): ReplaceIterator {
		return $marks->map( function ( $mark ) {
			return [ $mark->id => $mark->value ];
		} );
	}

	/**
	 * Set self::parent_id from given mark
	 *
	 * @param MarksView $mark
	 *
	 * @throws \Exception if the given mode is not defined.
	 */
	private function _setParent( MarksView $mark ): void {
		switch ( $this->mode ) {
			case 'trees':
				$this->parent_id = $mark->tree_id;

				return;

			case 'varieties':
				$this->parent_id = $mark->variety_id;

				return;

			case 'batches':
				$this->parent_id = $mark->batch_id;

				return;

			case 'convar':
				$this->parent_id = null !== $mark->variety_id ? $mark->variety_id : $mark->trees_view->variety_id;

				return;

			default:
				throw new \Exception( "The mode '{$this->mode}' is not defined." );
		}
	}

	/**
	 * Return array with count, avg, min, max, median and std (standard deviation) from given values
	 *
	 * @param array $values
	 * @param string $cast
	 *
	 * @return array stats
	 */
	private function _calculateStats( array $values, string $cast = null ): array {
		// typecast the values first, since they are all saved as strings in the db
		if ( $cast ) {
			foreach ( $values as &$value ) {
				settype( $value, $cast );
			}
		}

		// calculate reused stats
		$count = count( $values );
		$avg   = (float) array_sum( $values ) / $count;

		// sort values now and only once (for min, max and median)
		sort( $values );

		// calculate stats
		$stats['count']  = $count;
		$stats['avg']    = $avg;
		$stats['min']    = $values[0];
		$stats['max']    = $values[ $count - 1 ];
		$stats['median'] = $this->_median( $values, $count );
		$stats['std']    = $this->_stdDev( $values, $avg, $count );

		return $stats;
	}

	/**
	 * Calculate median
	 *
	 * @param array $sortedArray values must be in ascending order
	 * @param int $count number of items in $sortedArray
	 *
	 * @return float
	 */
	private function _median( array $sortedArray, int $count ): float {
		if ( $count <= 1 ) {
			return $sortedArray[0];
		}

		$middle = (int) ( $count / 2 );

		if ( $count % 2 ) {
			return $sortedArray[ $middle ];
		}

		return (float) ( ( $sortedArray[ $middle - 1 ] + $sortedArray[ $middle ] ) / 2 );
	}

	/**
	 * Calculate standard deviation
	 *
	 * @param array $values
	 * @param float $avg
	 * @param int $count
	 *
	 * @return float
	 */
	private function _stdDev( array $values, float $avg, int $count ): float {
		if ( $count <= 1 ) {
			return 0;
		}

		$tmp = array_map( function ( $v ) use ( $avg ) {
			return pow( ( $v - $avg ), 2 );
		}, $values );
		$var = array_sum( $tmp ) / $count;

		return sqrt( $var );
	}
}
