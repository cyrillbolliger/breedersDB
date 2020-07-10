<?php

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Validation\Validation;
use Cake\Event\Event;
use ArrayObject;

/**
 * MarkValues Model
 *
 * @property \Cake\ORM\Association\BelongsTo $MarkFormProperties
 * @property \Cake\ORM\Association\BelongsTo $Marks
 *
 * @method \App\Model\Entity\MarkValue get( $primaryKey, $options = [] )
 * @method \App\Model\Entity\MarkValue newEntity( $data = null, array $options = [] )
 * @method \App\Model\Entity\MarkValue[] newEntities( array $data, array $options = [] )
 * @method \App\Model\Entity\MarkValue|bool save( \Cake\Datasource\EntityInterface $entity, $options = [] )
 * @method \App\Model\Entity\MarkValue patchEntity( \Cake\Datasource\EntityInterface $entity, array $data, array $options = [] )
 * @method \App\Model\Entity\MarkValue[] patchEntities( $entities, array $data, array $options = [] )
 * @method \App\Model\Entity\MarkValue findOrCreate( $search, callable $callback = null, $options = [] )
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MarkValuesTable extends Table {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 *
	 * @return void
	 */
	public function initialize( array $config ) {
		parent::initialize( $config );

		$this->setTable( 'mark_values' );
		$this->setDisplayField( 'id' );
		$this->setPrimaryKey( 'id' );

		$this->addBehavior( 'Timestamp' );

		$this->belongsTo( 'MarkFormProperties', [
			'foreignKey' => 'mark_form_property_id',
			'joinType'   => 'INNER'
		] );
		$this->belongsTo( 'Marks', [
			'foreignKey' => 'mark_id',
			'joinType'   => 'INNER'
		] );
	}

	/**
	 * Modify data before saving
	 *
	 * @param Event $event
	 * @param ArrayObject $data
	 * @param ArrayObject $options
	 */
	public function beforeMarshal( Event $event, ArrayObject &$data, ArrayObject $options ) {
		// convert date data into the yyyy-mm-dd format
		$type = $this->MarkFormProperties->get( $data['mark_form_property_id'] )->field_type;

		if ( 'DATE' === $type ) {
			$tmp           = preg_split( '/[.-\/]/', $data['value'] );
			$data['value'] = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0];
		}
	}

	/**
	 * Default validation rules.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 *
	 * @return \Cake\Validation\Validator
	 */
	public function validationDefault( Validator $validator ) {
		$validator
			->integer( 'id' )
			->allowEmptyString( 'id', 'create' )
			->add( 'id', 'unique', [ 'rule' => 'validateUnique', 'provider' => 'table' ] );

		$validator
			->requirePresence( 'value', 'create' )
			->notEmptyString( 'value' )
			->add( 'value', 'custom', [
				'rule'    => function ( $value, $context ) {
					return $this->_validateValue( $value, $context );
				},
				'message' => __( 'The validation rules of this data type has been violated. Please check data type again.' ),
			] );

		$validator
			->boolean( 'exceptional_mark' )
			->requirePresence( 'exceptional_mark', 'create' )
			->notEmptyString( 'exceptional_mark' );

		return $validator;
	}

	/**
	 * Validation rules for the value column
	 *
	 * @param $value
	 * @param $context
	 *
	 * @return bool
	 */
	protected function _validateValue( $value, $context ) {
		$mark_form_property = $this->MarkFormProperties->get( $context['data']['mark_form_property_id'] );
		if ( $mark_form_property ) {
			switch ( $mark_form_property->field_type ) {
				case 'INTEGER':
					return Validation::isInteger( $value )
					       && (int) $value >= (int) $mark_form_property->validation_rule['min']
					       && (int) $value <= (int) $mark_form_property->validation_rule['max'];
					break;

				case 'FLOAT':
					return Validation::decimal( $value )
					       && (float) $value >= (float) $mark_form_property->validation_rule['min']
					       && (float) $value <= (float) $mark_form_property->validation_rule['max'];
					break;

				case 'VARCHAR':
					return Validation::notBlank( $value )
					       && Validation::maxLength( $value, 255 );
					break;

				case 'BOOLEAN':
					return Validation::boolean( $value );
					break;

				case 'DATE':
					return Validation::date( $value, 'ymd' );
					break;

				default:
					return false;
					break;
			}
		} else {
			return false;
		}
	}

	/**
	 * Returns a rules checker object that will be used for validating
	 * application integrity.
	 *
	 * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
	 *
	 * @return \Cake\ORM\RulesChecker
	 */
	public function buildRules( RulesChecker $rules ) {
		$rules->add( $rules->isUnique( [ 'id' ] ) );
		$rules->add( $rules->existsIn( [ 'mark_form_property_id' ], 'MarkFormProperties' ) );
		$rules->add( $rules->existsIn( [ 'mark_id' ], 'Marks' ) );

		return $rules;
	}
}
