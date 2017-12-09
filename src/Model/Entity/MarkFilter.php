<?php
/**
 * Created by PhpStorm.
 * User: cyrillbolliger
 * Date: 25.11.17
 * Time: 21:14
 */

namespace App\Model\Entity;


class MarkFilter {
	/**
	 * The mode how the filter works.
	 *
	 * Possible values:
	 * - 'all': for string values. the value must match all strings using the operator
	 * - 'any': for string values. the value must match at least one string using the operator
	 * - 'count': numerical values. the value must match the count number using the operator
	 * - 'avg': numerical values. the value must match the avg number using the operator
	 * - 'min': numerical values. the value must match the min number using the operator
	 * - 'max': numerical values. the value must match the max number using the operator
	 * - 'median': numerical values. the value must match the median number using the operator
	 * - 'std': numerical values. the value must match the std number using the operator
	 *
	 * @var string
	 */
	private $mode;
	
	/**
	 * The compare operator.
	 *
	 * Possible values:
	 * - ''
	 * - 'equal'
	 * - 'not_equal'
	 * - 'less'
	 * - 'less_or_equal'
	 * - 'greater'
	 * - 'greater_or_equal'
	 * - 'is_null'
	 * - 'is_not_null'
	 * - 'begins_with'
	 * - 'doesnt_begin_with'
	 * - 'contains'
	 * - 'doesnt_contain'
	 * - 'ends_with'
	 * - 'doesnt_end_with'
	 * - 'is_empty'
	 * - 'is_not_empty'
	 *
	 * @var string
	 */
	private $operator;
	
	/**
	 * The value to test against
	 *
	 * @var mixed
	 */
	private $value;
	
	/**
	 * MarkFilter constructor.
	 *
	 * @param string $mode
	 * @param string $operator
	 * @param mixed $value
	 */
	public function __construct( string $mode, string $operator, $value ) {
		$this->mode     = $mode;
		$this->operator = $operator;
		$this->value    = $value;
	}
	
	/**
	 * Test the given mark against this filter.
	 *
	 * @param AggregatedMark $mark
	 *
	 * @return bool
	 *
	 * @throws \Exception if the marks operator is not defined
	 */
	public function test( AggregatedMark $mark ): bool {
		// if no filtering was defined
		if ( '' === $this->operator ) {
			return true;
		}
		
		// if filter mode = all
		if ( 'all' === $this->mode ) {
			return $mark->values->every( function ( $value ) {
				return $this->_testValue( array_values( $value )[0] );
			} );
		}
		
		// if filter mode = any
		if ( 'any' === $this->mode ) {
			return $mark->values->some( function ( $value ) {
				return $this->_testValue( array_values( $value )[0] );
			} );
		}
		
		return $this->_testValue( $mark->value->{$this->mode} );
	}
	
	/**
	 * Test a given value against self::value using self::operator.
	 *
	 * @param $value
	 *
	 * @return bool
	 * @throws \Exception if the operator is not defined
	 */
	private function _testValue( $value ): bool {
		// make sure we compare equal types
		settype( $this->value, gettype( $value ) );
		
		switch ( $this->operator ) {
			case 'equal':
				return $value === $this->value;
			case 'not_equal':
				return $value !== $this->value;
			case 'less':
				return $value < $this->value;
			case 'less_or_equal':
				return $value <= $this->value;
			case 'greater':
				return $value > $this->value;
			case 'greater_or_equal':
				return $value >= $this->value;
			case 'is_null':
				return $value === null;
			case 'is_not_null':
				return $value !== null;
			case 'begins_with':
				return 0 === strpos( $value, $this->value );
			case 'doesnt_begin_with':
				return 0 !== strpos( $value, $this->value );
			case 'contains':
				return false !== strpos( $value, $this->value );
			case 'doesnt_contain':
				return false === strpos( $value, $this->value );
			case 'ends_with':
				return strlen( $value ) - strlen( $this->value ) === strpos( $value, $this->value );
			case 'doesnt_end_with':
				return strlen( $value ) - strlen( $this->value ) !== strpos( $value, $this->value );
			case 'is_empty':
				return in_array( $value, [ '', null ] );
			case 'is_not_empty':
				return ! in_array( $value, [ '', null ] );
			default:
				throw new \Exception( "The operator {$this->operator} is not defined." );
		}
	}
}