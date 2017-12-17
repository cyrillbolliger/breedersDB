<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * MarkFormProperty Entity
 *
 * @property int $id
 * @property string $name
 * @property string $validation_rule
 * @property string $field_type
 * @property string $note
 * @property array $aggregation_functions
 * @property string $input_type
 * @property array $operators
 * @property int $mark_form_property_type_id
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\MarkFormPropertyType $mark_form_property_type
 * @property \App\Model\Entity\MarkFormField[] $mark_form_fields
 * @property \App\Model\Entity\MarkValue[] $mark_values
 */
class MarkFormProperty extends Entity {
	
	/**
	 * Fields that can be mass assigned using newEntity() or patchEntity().
	 *
	 * Note that when '*' is set to true, this allows all unspecified fields to
	 * be mass assigned. For security purposes, it is advised to set '*' to false
	 * (or remove it), and explicitly make individual fields accessible as needed.
	 *
	 * @var array
	 */
	protected $_accessible = [
		'*'  => true,
		'id' => false
	];
	
	/**
	 * Return an array with all possible aggregation types for the MarkFormProperty::field_type.
	 * The keys are holding the internal function name, the values the translated names for the form.
	 *
	 * @return array
	 *
	 * @throws \Exception if the MarkFormProperty::field_type is not defined.
	 */
	protected function _getAggregationFunctions(): array {
		if ( $this->_getIsNumerical() ) {
			return [
				'count'  => __( 'Count' ),
				'avg'    => __( 'Average' ),
				'min'    => __( 'Min' ),
				'max'    => __( 'Max' ),
				'median' => __( 'Median' ),
				'std'    => __( 'Standard deviation' ),
			];
		}
		
		return [
			'all' => __x( 'every', 'Every' ),
			'any' => __x( 'at least one', 'Any' ),
		];
		
	}
	
	/**
	 * Return true if entity has field_type INTEGER or FLOAT
	 *
	 * @return bool
	 */
	protected function _getIsNumerical(): bool {
		return in_array( $this->_properties['field_type'], [ 'INTEGER', 'FLOAT' ] );
	}
	
	/**
	 * Return a string with the input type
	 *
	 * @return string
	 *
	 * @throws \Exception if the MarkFormProperty::field_type is not defined.
	 */
	protected function _getInputType(): string {
		switch ( $this->_properties['field_type'] ) {
			case 'INTEGER':
				return 'number';
			
			case 'FLOAT':
				return 'number';
			
			case 'VARCHAR':
				return 'text';
			
			case 'BOOLEAN':
				return 'radio';
			
			case 'DATE':
				return 'date';
			
			default:
				throw new \Exception( "The field type '{$this->_properties['field_type']}' is not defined." );
		}
	}
	
	/**
	 * Return an array with all possible compare operators for the MarkFormProperty::field_type.
	 * The keys are holding the internal function name, the values the translated names for the form.
	 *
	 * @return array
	 *
	 * @throws \Exception if the MarkFormProperty::field_type is not defined.
	 */
	protected function _getOperators(): array {
		switch ( $this->_properties['field_type'] ) {
			case 'INTEGER': // fall through
			case 'FLOAT':
				return [
					'equal'            => __( 'equal' ),
					'not_equal'        => __( 'not equal' ),
					'less'             => __( 'less' ),
					'less_or_equal'    => __( 'less or equal' ),
					'greater'          => __( 'greater' ),
					'greater_or_equal' => __( 'greater or equal' ),
					'is_null'          => __( 'is null' ),
					'is_not_null'      => __( 'is not null' ),
				];
			
			case 'VARCHAR': // fall through
			case 'DATE':
				return [
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
			
			case 'BOOLEAN':
				return [
					'equal'     => __( 'equal' ),
					'not_equal' => __( 'not equal' ),
				];
			
			default:
				throw new \Exception( "The field type '{$this->_properties['field_type']}' is not defined." );
		}
	}
	
	/**
	 * Return array with possible values for boolean fields. Return null for all other fields.
	 *
	 * @return array|null
	 */
	protected function _getValues() {
		if ( 'BOOLEAN' === $this->_properties['field_type'] ) {
			return [ 0 => __( 'True' ), 1 => __( 'False' ) ];
		}
		
		return null;
	}
}