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
 * @property string $default_value
 * @property string $note
 * @property array $aggregation_functions
 * @property string $input_type
 * @property array $operators
 * @property int $mark_form_property_type_id
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property boolean tree_property
 * @property boolean variety_property
 * @property boolean batch_property
 * @property-read object $number_constraints
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

    protected $_virtual = ['number_constraints'];

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
        if (! isset($this->_fields['field_type'])) {
            return false;
        }

		return in_array( $this->_fields['field_type'], [ 'INTEGER', 'FLOAT' ] );
	}

    /**
     * Return true if entity has field_type VARCHAR or DATE
     *
     * @return bool
     */
    protected function _getIsText(): bool {
        return in_array( $this->_fields['field_type'], [ 'VARCHAR', 'DATE' ] );
    }

	/**
	 * Return a string with the input type
	 *
	 * @return string
	 *
	 * @throws \Exception if the MarkFormProperty::field_type is not defined.
	 */
	protected function _getInputType(): string {
        return match ($this->_fields['field_type']) {
            'FLOAT',
            'INTEGER' => 'number',
            'PHOTO',
            'VARCHAR' => 'text',
            'BOOLEAN' => 'radio',
            'DATE' => 'date',
            default => throw new \Exception("The field type '{$this->_fields['field_type']}' is not defined."),
        };
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
		switch ( $this->_fields['field_type'] ) {
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

            case 'PHOTO':
                return [
                    'is_not_empty'      => __( 'has photo' ),
                    'is_empty'          => __( "doesn't have photo" ),
                ];

			default:
				throw new \Exception( "The field type '{$this->_fields['field_type']}' is not defined." );
		}
	}

	/**
	 * Return array with possible values for boolean fields. Return null for all other fields.
	 *
	 * @return array|null
	 */
	protected function _getValues() {
		if ( 'BOOLEAN' === $this->_fields['field_type'] ) {
			return [ 1 => __( 'True' ), 0 => __( 'False' ) ];
		}

		return null;
	}

	protected function _getNumberConstraints() {
	    if (! $this->_getIsNumerical()) {
	        return null;
        }

	    if ('INTEGER' === $this->_fields['field_type']) {
            $min = (int)$this->validation_rule['min'];
            $max = (int)$this->validation_rule['max'];
            $step = (int)$this->validation_rule['step'];
        } else {
            $min = (float)$this->validation_rule['min'];
            $max = (float)$this->validation_rule['max'];
            $step = (float)$this->validation_rule['step'];
        }

	    return (object) [
	        'min' => $min,
            'max' => $max,
            'step' => $step
        ];
    }
}
