<?php

namespace App\Model\Table;

use App\Model\Rule\IsNotReferredBy;
use ArrayObject;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Event\Event;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Utility\Text;
use Cake\Validation\Validator;


/**
 * MarkFormProperties Model
 *
 * @property \Cake\ORM\Association\BelongsTo $MarkFormPropertyTypes
 * @property \Cake\ORM\Association\HasMany $MarkFormFields
 * @property \Cake\ORM\Association\HasMany $MarkValues
 *
 * @method \App\Model\Entity\MarkFormProperty get( $primaryKey, $options = [] )
 * @method \App\Model\Entity\MarkFormProperty newEntity( $data = null, array $options = [] )
 * @method \App\Model\Entity\MarkFormProperty[] newEntities( array $data, array $options = [] )
 * @method \App\Model\Entity\MarkFormProperty|bool save( \Cake\Datasource\EntityInterface $entity, $options = [] )
 * @method \App\Model\Entity\MarkFormProperty patchEntity( \Cake\Datasource\EntityInterface $entity, array $data, array $options = [] )
 * @method \App\Model\Entity\MarkFormProperty[] patchEntities( $entities, array $data, array $options = [] )
 * @method \App\Model\Entity\MarkFormProperty findOrCreate( $search, callable $callback = null, $options = [] )
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MarkFormPropertiesTable extends Table {
	private $namesBySlug;

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 *
	 * @return void
	 */
	public function initialize( array $config ): void {
		parent::initialize( $config );

		$this->setTable( 'mark_form_properties' );
		$this->setDisplayField( 'name' );
		$this->setPrimaryKey( 'id' );

		$this->addBehavior( 'Timestamp' );

		$this->belongsTo( 'MarkFormPropertyTypes', [
			'foreignKey' => 'mark_form_property_type_id',
			'joinType'   => 'INNER'
		] );
		$this->hasMany( 'MarkFormFields', [
			'foreignKey' => 'mark_form_property_id'
		] );
		$this->hasMany( 'MarkValues', [
			'foreignKey' => 'mark_form_property_id'
		] );
		$this->hasMany( 'MarkScannerCodes', [
			'foreignKey' => 'mark_form_property_id'
		] );
	}

	/**
	 * Default validation rules.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 *
	 * @return \Cake\Validation\Validator
	 */
	public function validationDefault( Validator $validator ): \Cake\Validation\Validator {
		$validator
			->integer( 'id' )
			->allowEmptyString( 'id', __('This field is required'), 'create' );

		$validator
			->requirePresence( 'name', 'create' )
			->notEmptyString( 'name' )
			->add( 'name', 'unique', [
				'rule'     => 'validateUnique',
				'provider' => 'table',
				'message'  => __( 'This name has already been used.' ),
			] )
            ->add('name', 'custom', [
                'rule' => static fn($value) => !str_contains($value, '>'),
                'message' => __('The ">" sign is not allowed in the name.'),
            ]);

		$validator
			->requirePresence( 'field_type', 'create' )
			->notEmptyString( 'field_type' );

		$validator
			->requirePresence( 'validation_rule', 'create' )
			->allowEmptyString( 'validation_rule' );

		$validator
			->add( 'min', 'custom', [
				'rule'    => function ( $value, $context ) {
					if ( in_array( $context['data']['field_type'], [ 'INTEGER', 'FLOAT' ] ) ) {
						return $value < $context['data']['max'];
					} else {
						return true;
					}
				},
				'message' => __( 'The min value is required and must be smaller than the max value' ),
			] );

		$validator
			->add( 'max', 'custom', [
				'rule'    => function ( $value, $context ) {
					if ( in_array( $context['data']['field_type'], [ 'INTEGER', 'FLOAT' ] ) ) {
						return $value > $context['data']['min'];
					} else {
						return true;
					}
				},
				'message' => __( 'The max value is required and must be greater than the max value' ),
			] );

		$validator
			->add( 'step', 'custom', [
				'rule'    => function ( $value, $context ) {
					if ( in_array( $context['data']['field_type'], [ 'INTEGER', 'FLOAT' ] ) ) {
						return ( $value > 0 ) && ( $value <= ( $context['data']['max'] - $context['data']['min'] ) );
					} else {
						return true;
					}
				},
				'message' => __( 'The step value is required and must be greater than zero and smaller or equal to the difference between the max and the min value.' ),
			] );

		$validator
			->add( 'tree_property', 'custom', [
				'rule'    => function ( $value, $context ) {
					if ( $value ) {
						return true;
					} else {
						return $context['data']['variety_property'] || $context['data']['batch_property'];
					}
				},
				'message' => __( 'Select at least one domain' ),
			] );

		$validator
			->add( 'variety_property', 'custom', [
				'rule'    => function ( $value, $context ) {
					if ( $value ) {
						return true;
					} else {
						return $context['data']['tree_property'] || $context['data']['batch_property'];
					}
				},
				'message' => __( 'Select at least one domain' ),
			] );

		$validator
			->add( 'batch_property', 'custom', [
				'rule'    => function ( $value, $context ) {
					if ( $value ) {
						return true;
					} else {
						return $context['data']['variety_property'] || $context['data']['tree_property'];
					}
				},
				'message' => __( 'Select at least one domain' ),
			] );

		$validator
			->requirePresence( 'mark_form_property_type_id', 'create' )
			->notEmptyString( 'mark_form_property_type_id' );

		return $validator;
	}

	/**
	 * Returns a rules checker object that will be used for validating
	 * application integrity.
	 *
	 * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
	 *
	 * @return \Cake\ORM\RulesChecker
	 */
	public function buildRules( RulesChecker $rules ): \Cake\ORM\RulesChecker {
		$rules->add( $rules->isUnique( [ 'id' ] ) );
		$rules->add( $rules->existsIn( [ 'mark_form_property_type_id' ], 'MarkFormPropertyTypes' ) );
		$rules->add( $rules->isUnique( [ 'name' ],
			__( 'This name has already been used. Please use a unique name.' ) ) );

		$rules->addDelete( new IsNotReferredBy( [ 'MarkFormFields' => 'mark_form_property_id' ] ), 'isNotReferredBy' );
		$rules->addDelete( new IsNotReferredBy( [ 'MarkValues' => 'mark_form_property_id' ] ), 'isNotReferredBy' );
		$rules->addDelete( new IsNotReferredBy( [ 'MarkScannerCodes' => 'mark_form_property_id' ] ),
			'isNotReferredBy' );

		return $rules;
	}

	public function beforeMarshal( Event $event, ArrayObject $data, ArrayObject $options ) {
		$data['validation_rule'] = $this->buildValidationRuleFieldData( $data );

        if (isset($data['default_value']) && $data['default_value'] !== null) {
            $data['default_value'] = trim($data['default_value']);
            $data['default_value'] = $data['default_value'] === '' ? null : $data['default_value'];
        }
	}

	/**
	 * Return a JSON with the validation rules
	 *
	 * @param  ArrayObject $data the request data
	 *
	 * @return String JSON object with the validation rule
	 */
	public function buildValidationRuleFieldData( ArrayObject $data ) {
		$validation_rule = array();

		if ( in_array( $data['field_type'], [ 'INTEGER', 'FLOAT' ] ) ) {
			$validation_rule = [
				'min'  => isset( $data['min'] ) ? $data['min'] : PHP_INT_MIN,
				'max'  => isset( $data['max'] ) ? $data['max'] : PHP_INT_MAX,
				'step' => isset( $data['step'] ) ? $data['step'] : 1,
			];
		}

		return $validation_rule;
	}

	/**
	 * Return array with field type as key and description as value
	 *
	 * @return array
	 */
	public function getFieldTypes() {
		return [
			'INTEGER' => __( 'Integer' ),
			'FLOAT'   => __( 'Floating-point number' ),
			'VARCHAR' => __( 'Text ( < 255 characters)' ),
			'BOOLEAN' => __( 'Boolean' ),
			'DATE'    => __( 'Date' ),
            'PHOTO'   => __( 'Photo' ),
		];
	}

	/**
	 * Return query filtered by given search term searching the name
	 *
	 * @param string $term
	 *
	 * @return \Cake\ORM\Query
	 */
	public function filter( string $term ) {
		$where = trim( $term );

		$query = $this->find()
		              ->contain( [ 'MarkFormPropertyTypes' ] )
		              ->where( [ 'MarkFormProperties.name LIKE' => '%' . $where . '%' ] );

		return $query;
	}

	/**
	 * Return the name of a property by its slug
	 *
	 * @param string $slug
	 *
	 * @return string
	 *
	 * @throws \Exception if no name matches the given slug.
	 */
	public function getNameBySlug( string $slug ): string {
		if ( empty( $this->namesBySlug ) ) {
			$names = $this->find()->select( 'name' );

			$this->namesBySlug = [];
			foreach ( $names as $item ) {
				$s                       = Text::slug( $item->name );
				$this->namesBySlug[ $s ] = $item->name;
			}
		}

		if ( !array_key_exists($slug, $this->namesBySlug)) {
			throw new \Exception( "There is no mark property belonging to the given slug '{$this->namesBySlug}'" );
		}

		return $this->namesBySlug[ $slug ];
	}

	protected function _initializeSchema( TableSchemaInterface $schema ): TableSchemaInterface {
		$schema->setColumnType( 'validation_rule', 'json' );

		return $schema;
	}
}
