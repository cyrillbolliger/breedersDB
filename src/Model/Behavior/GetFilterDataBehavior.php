<?php
/**
 * Created by PhpStorm.
 * User: cyrillbolliger
 * Date: 14.09.17
 * Time: 20:08
 */

namespace App\Model\Behavior;

use App\Utility\MarksAggregatorUtility;
use Cake\ORM\Behavior;
use Cake\ORM\TableRegistry;
use Cake\Utility\Text;

class GetFilterDataBehavior extends Behavior {
	/**
	 * Return data for the query where builder
	 *
	 * @return array
	 */
	public function getFilterData() {
		$queries = TableRegistry::get( 'Queries' );
		
		$tables        = array_keys( $queries->getViewNames() );
		$tables_fields = $queries->getFieldTypeMapOf( $tables );
		
		// add normal filter data
		foreach ( $tables_fields as $table => $table_fields ) {
			foreach ( $table_fields as $field => $type ) {
				$fields[ $table ][] = $this->_getFieldFilterData( $table, $field, $type );
			}
		}
		
		return $fields;
	}
	
	/**
	 * Return array with filter data according to the needs of http://querybuilder.js.org/#filters
	 *
	 * @param string $table
	 * @param string $field
	 * @param string $type
	 *
	 * @return array
	 */
	private function _getFieldFilterData( string $table, string $field, string $type ): array {
		$queries = TableRegistry::get( 'Queries' );
		
		$data['id']    = $table . '.' . $field;
		$data['label'] = $queries->translateFields( $data['id'] );
		$data['type']  = $this->_getFieldTypeForFilter( $type );
		$data['input'] = $this->_getFilterFieldInputType( $table, $field, $type );
		
		$this->_addValidator( $data );
		$this->_addRadioButtonProperties( $data );
		
		if ( 'select' === $data['input'] ) {
			$data['values']    = $this->_getDistinctValuesOf( $table, $field );
			$data['operators'] = [ 'equal', 'not_equal', 'is_empty', 'is_not_empty' ];
		}
		
		return $data;
	}
	
	/**
	 * Return the type our query where builder understands from given cakeish db type or Mark Property Data Type
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	private function _getFieldTypeForFilter( string $type ): string {
		$cast = [
			/* cakeisch db types below */
			'string'       => 'string',
			'text'         => 'string',
			'integer'      => 'integer',
			'smallinteger' => 'integer',
			'tinyinteger'  => 'integer',
			'biginteger'   => 'integer',
			'float'        => 'double',
			'decimal'      => 'double',
			'boolean'      => 'boolean',
			'date'         => 'date',
			'datetime'     => 'datetime',
			'timestamp'    => 'integer',
			'time'         => 'time',
			/* Mark Property data types below */
			'INTEGER'      => 'integer',
			'VARCHAR'      => 'string',
			'BOOLEAN'      => 'boolean',
			'DATE'         => 'date',
			'FLOAT'        => 'double',
		];
		
		return $cast[ $type ];
	}
	
	/**
	 * Return array with filter data according to the needs of http://querybuilder.js.org/#filters
	 *
	 * @param string $tablename
	 * @param string $field
	 * @param string $type
	 *
	 * @return string
	 */
	private function _getFilterFieldInputType( string $tablename, string $field, string $type ): string {
		if ( in_array( $type,
			[ 'integer', 'smallinteger', 'tinyinteger', 'biginteger', 'float', 'decimal', 'timestamp' ] ) ) {
			return 'number';
		}
		
		$table = TableRegistry::get( $tablename );
		if ( in_array( $field, $table->getBooleanFields() ) ) {
			return 'radio';
		}
		if ( in_array( $field, $table->getSelectFields() ) ) {
			return 'select';
		}
		
		return 'text';
	}
	
	/**
	 * Add a where query builder validator object
	 *
	 * @param array $field
	 *
	 * @throws \Exception
	 */
	private function _addValidator( array &$field ): void {
		switch ( $field['type'] ) {
			case 'string':
				$field['validation'] = [];
				break;
			
			case 'integer':
				$field['validation'] = [
					'step'              => 1,
					'min'               => PHP_INT_MIN,
					'max'               => PHP_INT_MAX,
					'allow_empty_value' => false,
					'messages'          => [
						'number_nan'         => __( 'Please enter a number' ),
						'number_not_integer' => __( 'Only integers allowed' ),
						'step'               => __( 'Only integers allowed' ),
						'allow_empty_value'  => __( 'Please enter a number' ),
						'min'                => sprintf( __( 'The given number must not be smaller then %s' ),
							PHP_INT_MIN ),
						'man'                => sprintf( __( 'The given number must not be greater then %s' ),
							PHP_INT_MAX ),
					]
				];
				break;
			
			case 'double':
				$field['validation'] = [
					'step'              => 'any',
					'allow_empty_value' => false,
					'messages'          => [
						'number_nan'        => __( 'Please enter a number' ),
						'number_not_double' => __( 'Only numbers allowed' ),
						'allow_empty_value' => __( 'Please enter a number' ),
					]
				];
				break;
			
			case 'boolean':
				$field['validation'] = [];
				break;
			
			case 'date':
				$field['validation'] = [
					'format'            => __x( 'moment.js date format', 'DD.MM.YYYY' ),
					'allow_empty_value' => false,
					'messages'          => [
						'format'            => __x( 'moment.js date format',
							'The given date must match the format DD.MM.YYYY' ),
						'allow_empty_value' => __( 'Please enter a date' ),
					]
				];
				break;
			
			case 'datetime':
				$field['validation'] = [
					'format'            => __x( 'moment.js datetime format', 'DD.MM.YYYY HH:mm:ss' ),
					'allow_empty_value' => false,
					'messages'          => [
						'format'            => __x( 'moment.js datetime format',
							'The given date and time must match the format DD.MM.YYYY HH:mm:ss' ),
						'allow_empty_value' => __( 'Please enter date and time' ),
					]
				];
				break;
			
			case 'time':
				$field['validation'] = [
					'format'            => __x( 'moment.js time format', 'HH:mm:ss' ),
					'allow_empty_value' => false,
					'messages'          => [
						'format'            => __x( 'moment.js time format',
							'The given time must match the format HH:mm:ss' ),
						'allow_empty_value' => __( 'Please enter a time' ),
					]
				];
				break;
			
			default:
				throw new \Exception( 'Unknown type given.' );
		}
	}
	
	/**
	 * Add the radio button properties to given filter data, if needed.
	 *
	 * @param $data
	 */
	private function _addRadioButtonProperties( &$data ) {
		if ( 'radio' === $data['input'] ) {
			$data['values']        = [ 1 => __( 'Yes' ), 0 => __( 'No' ) ];
			$data['operators']     = [ 'equal' ];
			$data['default_value'] = 1;
		}
	}
	
	/**
	 * Return array with the possible select values of the given table.field
	 *
	 * @param string $tablename
	 * @param string $field
	 *
	 * @return array
	 */
	private function _getDistinctValuesOf( string $tablename, string $field ): array {
		$table  = TableRegistry::get( $tablename );
		$tmp    = $table->find()->select( [ $field ] )->distinct()->orderAsc( $field )->toArray();
		$values = [];
		foreach ( $tmp as $item ) {
			if ( empty( $item->$field ) ) {
				continue;
			}
			$values[ $item->$field ] = $item->$field;
		}
		
		return $values;
	}
	
	/**
	 * Return array with the mark properties using the following structure:
	 * [
	 *  'name' => 'H_Gesamteindruck Frucht',
	 *  'id' => 'H_Gesamteindruck Frucht',
	 *  'label' => 'H_Gesamteindruck Frucht',
	 *  'db_type' => 'INTEGER',
	 *  'type' => 'integer',
	 *  'aggregations' => [
	 *      'count' => 'Count',
	 *      'avg' => 'Average',
	 *      'min' => 'Min',
	 *      'max' => 'Max',
	 *      'median' => 'Median',
	 *      'std' => 'Standard deviation'
	 *  ]
	 * ]
	 *
	 * @see MarksAggregatorUtility::getAggregationFunctions() for details about the aggregations.
	 *
	 * @return array
	 */
	public function getMarksSelectorData() {
		// get mark properties
		$properties = $this->_getDistinctValuesOf( 'MarksView', 'name' );
		
		$fields = [];
		foreach ( $properties as $property ) {
			$markProperty = $this->_getMarkPropertyFieldFilterData( $property );
			
			// we must overwrite this to make sure we don't break serialization of forms
			$markProperty['id'] = Text::slug( $property );
			
			$this->_addMarkPropertyAggregations( $markProperty );
			$this->_addMarkPropertyOperator( $markProperty );
			
			$fields[] = $markProperty;
		}
		
		return $fields;
	}
	
	/**
	 * Return array with mark property filter data according to the needs of http://querybuilder.js.org/#filters
	 *
	 * @param string $field
	 *
	 * @return array
	 */
	private function _getMarkPropertyFieldFilterData( string $field ): array {
		$markProperty = [
			'name'  => $field,
			'id'    => 'MarkProperty.' . $field,
			'label' => __( 'Mark Property' ) . ' -> ' . $field,
		];
		
		$this->_addMarkPropertyType( $markProperty );
		$this->_addMarkPropertyFilterInputType( $markProperty );
		
		$this->_addValidator( $markProperty );
		
		$this->_addRadioButtonProperties( $markProperty );
		
		return $markProperty;
	}
	
	/**
	 * Add a key 'type' with the the property type as value to the given array.
	 * Also add a key 'db_type with the property type as stored in the db as value.
	 * The array must contain a key 'name' with its property name as stored in the db.
	 *
	 * @see GetFilterDataBehavior::_getFieldTypeForFilter() for more information about the types.
	 *
	 * @param array $markProperty
	 */
	private function _addMarkPropertyType( array &$markProperty ) {
		$marks                   = TableRegistry::get( 'MarksView' );
		$type                    = $marks->find()->select( 'field_type' )->where( [ 'name' => $markProperty['name'] ] )->firstOrFail()->field_type;
		$markProperty['db_type'] = $type;
		$markProperty['type']    = $this->_getFieldTypeForFilter( $type );
	}
	
	/**
	 * Add a key 'input' with the the input type as value to the given array.
	 * The array must contain a key 'type' with its property type as added
	 * by @see GetFilterDataBehavior::_addMarkPropertyType().
	 *
	 * @param array $markProperty
	 */
	private function _addMarkPropertyFilterInputType( array &$markProperty ) {
		$typeInputTypeMap = [
			'integer' => 'number',
			'double'  => 'number',
			'string'  => 'text',
			'boolean' => 'radio',
			'date'    => 'text'
		];
		
		$markProperty['input'] = $typeInputTypeMap[ $markProperty['type'] ];
	}
	
	/**
	 * Add an array with the possible aggregation functions to the given array using the key 'aggregations'
	 *
	 * @param array $markProperty
	 */
	private function _addMarkPropertyAggregations( array &$markProperty ): void {
		$markProperty['aggregations'] = MarksAggregatorUtility::getAggregationFunctions( $markProperty['db_type'] );
	}
	
	/**
	 * Add possible operators to mark property
	 *
	 * @param array $markProperty
	 */
	private function _addMarkPropertyOperator( array &$markProperty ) {
		$operators = [];
		switch ( $markProperty['type'] ) {
			case 'integer': // fall through
			case 'date': // fall through
			case 'double':
				$operators = [
					'equal'            => __( 'equal' ),
					'not_equal'        => __( 'not equal' ),
					'less'             => __( 'less' ),
					'less_or_equal'    => __( 'less or equal' ),
					'greater'          => __( 'greater' ),
					'greater_or_equal' => __( 'greater or equal' ),
					'is_null'          => __( 'is null' ),
					'is_not_null'      => __( 'is not null' ),
				];
				break;
			case 'string':
				$operators = [
					'equal'             => __( 'equal' ),
					'not_equal'         => __( 'not equal' ),
					'begins_with'       => __( 'begins with' ),
					'doesnt_begin_with' => __( "doesn't begin with" ),
					'contains'          => __( 'contains' ),
					'doenst_contain'    => __( "doesn't contain" ),
					'ends_with'         => __( 'end with' ),
					'doesnt_end_with'   => __( "doesn't end with" ),
					'is_empty'          => __( 'is empty' ),
					'is_not_empty'      => __( 'is not empty' ),
				];
				break;
			case 'boolean':
				$operators = [
					'equal' => __( 'equal' ),
				];
				break;
		}
		
		$markProperty['operators'] = $operators;
	}
	
	/**
	 * Return array with mark properties using the following structure
	 * to be compatible to the needs of http://querybuilder.js.org/#filters
	 *
	 * Structure:
	 * [
	 *  'id' => 'MarkProperty.K7_Feuerbrand',
	 *  'label' => 'Mark Property -> K7_Feuerbrand',
	 *  'type' => 'integer',
	 *  'input' => 'number',
	 *  'validation' => [
	 *      'step' => (int) 1,
	 *      'min' => (int) -9223372036854775808,
	 *      'max' => (int) 9223372036854775807,
	 *      'allow_empty_value' => false,
	 *      'messages' => [
	 *          'number_nan' => 'Please enter a number',
	 *          'number_not_integer' => 'Only integers allowed',
	 *          'step' => 'Only integers allowed',
	 *          'allow_empty_value' => 'Please enter a number',
	 *          'min' => 'The given number must not be smaller then -9223372036854775808',
	 *          'man' => 'The given number must not be greater then 9223372036854775807'
	 *      ]
	 *  ]
	 * ]
	 *
	 * @return array
	 */
	private function _getMarkPropertiesForFilter(): array {
		$fields = [];
		
		// get mark properties
		$table      = 'MarksView';
		$properties = $this->_getDistinctValuesOf( $table, 'name' );
		foreach ( $properties as $property ) {
			$fields[ $table ][] = $this->_getMarkPropertyFieldFilterData( $property );
		}
		
		return $fields;
	}
}