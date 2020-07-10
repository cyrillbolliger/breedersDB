<?php

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use App\Model\Rule\IsNotReferredBy;
use ArrayObject;
use Cake\Event\Event;


/**
 * Marks Model
 *
 * @property \Cake\ORM\Association\BelongsTo $MarkForms
 * @property \Cake\ORM\Association\BelongsTo $Trees
 * @property \Cake\ORM\Association\BelongsTo $Varieties
 * @property \Cake\ORM\Association\BelongsTo $Batches
 * @property \Cake\ORM\Association\HasMany $MarkValues
 *
 * @method \App\Model\Entity\Mark get( $primaryKey, $options = [] )
 * @method \App\Model\Entity\Mark newEntity( $data = null, array $options = [] )
 * @method \App\Model\Entity\Mark[] newEntities( array $data, array $options = [] )
 * @method \App\Model\Entity\Mark|bool save( \Cake\Datasource\EntityInterface $entity, $options = [] )
 * @method \App\Model\Entity\Mark patchEntity( \Cake\Datasource\EntityInterface $entity, array $data, array $options = [] )
 * @method \App\Model\Entity\Mark[] patchEntities( $entities, array $data, array $options = [] )
 * @method \App\Model\Entity\Mark findOrCreate( $search, callable $callback = null, $options = [] )
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MarksTable extends Table {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 *
	 * @return void
	 */
	public function initialize( array $config ) {
		parent::initialize( $config );

		$this->setTable( 'marks' );
		$this->setDisplayField( 'id' );
		$this->setPrimaryKey( 'id' );

		$this->addBehavior( 'Timestamp' );

		$this->belongsTo( 'MarkForms', [
			'foreignKey' => 'mark_form_id',
			'joinType'   => 'INNER'
		] );
		$this->belongsTo( 'Trees', [
			'foreignKey' => 'tree_id'
		] );
		$this->belongsTo( 'Varieties', [
			'foreignKey' => 'variety_id'
		] );
		$this->belongsTo( 'Batches', [
			'foreignKey' => 'batch_id'
		] );
		$this->hasMany( 'MarkValues', [
			'foreignKey' => 'mark_id'
		] );
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
			->allowEmpty( 'id', 'create' )
			->add( 'id', 'unique', [ 'rule' => 'validateUnique', 'provider' => 'table' ] );

		$validator
			->localizedTime( 'date', 'date' )
			->allowEmpty( 'date' );

		$validator
			->notEmpty( 'tree_id', __( 'Please select a tree' ), function ( $context ) {
				return empty( $context['data']['variety_id'] ) && empty( $context['data']['batch_id'] );
			} )
			->add( 'tree_id', 'custom', [
				'rule'    => function ( $value, $context ) {
					return empty( $context['data']['variety_id'] ) && empty( $context['data']['batch_id'] );
				},
				'message' => __( 'Variety and batch must not be selected' ),
			] );

		$validator
			->notEmpty( 'variety_id', __( 'Please select a variety' ), function ( $context ) {
				return empty( $context['data']['tree_id'] ) && empty( $context['data']['batch_id'] );
			} )
			->add( 'variety_id', 'custom', [
				'rule'    => function ( $value, $context ) {
					return empty( $context['data']['tree_id'] ) && empty( $context['data']['batch_id'] );
				},
				'message' => __( 'Tree and batch must not be selected' ),
			] );

		$validator
			->notEmpty( 'batch_id', __( 'Please select a batch' ), function ( $context ) {
				return empty( $context['data']['tree_id'] ) && empty( $context['data']['variety_id'] );
			} )
			->add( 'batch_id', 'custom', [
				'rule'    => function ( $value, $context ) {
					return empty( $context['data']['tree_id'] ) && empty( $context['data']['variety_id'] );
				},
				'message' => __( 'Tree and variety must not be selected' ),
			] );

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
	public function buildRules( RulesChecker $rules ) {
		$rules->add( $rules->isUnique( [ 'id' ] ) );
		$rules->add( $rules->existsIn( [ 'mark_form_id' ], 'MarkForms' ) );
		$rules->add( $rules->existsIn( [ 'tree_id' ], 'Trees' ) );
		$rules->add( $rules->existsIn( [ 'variety_id' ], 'Varieties' ) );
		$rules->add( $rules->existsIn( [ 'batch_id' ], 'Batches' ) );

		$rules->addDelete( new IsNotReferredBy( [ 'MarkValues' => 'mark_id' ] ), 'isNotReferredBy' );

		return $rules;
	}

	/**
	 * Reorder data in given array (from request) to enable marshalling the
	 * associated mark_values. Also check if it's an exceptional mark and set
	 * result.
	 *
	 * @param array $input
	 *
	 * @return array
	 */
	public function prepareToSaveAssociated( $input ) {
		$mark_values = array();

		if ( isset( $input['mark_form_fields']['mark_form_properties'] ) ) {
			foreach ( $input['mark_form_fields']['mark_form_properties'] as $mark_form_property_id => $value ) {
				if ( '' !== $value['mark_values']['value'] ) {
					$mark_values[] = [
						'value'                 => $value['mark_values']['value'],
						'mark_form_property_id' => $mark_form_property_id,
						'exceptional_mark'      => $this->_isMarkExceptional( $input['mark_form_id'],
							$mark_form_property_id ),
					];
				}
			}
		}

		if ( isset( $input['mark_form_fields'] ) ) {
			unset( $input['mark_form_fields'] );
		}

		if ( isset( $input['mark_form_properties'] ) ) {
			unset( $input['mark_form_properties'] );
		}

		return [
			'date'         => isset( $input['date'] ) ? $input['date'] : null,
			'author'       => isset( $input['author'] ) ? $input['author'] : null,
			'mark_form_id' => isset( $input['mark_form_id'] ) ? $input['mark_form_id'] : null,
			'tree_id'      => isset( $input['tree_id'] ) ? $input['tree_id'] : null,
			'variety_id'   => isset( $input['variety_id'] ) ? $input['variety_id'] : null,
			'batch_id'     => isset( $input['batch_id'] ) ? $input['batch_id'] : null,
			'mark_values'  => $mark_values,
		];
	}

	/**
	 * Return true if given mark_form_property_id is NOT on the mark form with
	 * the given mark_form_id. Else return false.
	 *
	 * @param int $mark_form_id
	 * @param int $mark_form_property_id
	 *
	 * @return boolean
	 */
	protected function _isMarkExceptional( int $mark_form_id, int $mark_form_property_id ) {
		return ! (bool) $this->MarkForms->MarkFormFields->find()
		                                                ->where( [ 'mark_form_id' => $mark_form_id ] )
		                                                ->andWhere( [ 'mark_form_property_id' => $mark_form_property_id ] )
		                                                ->count();
	}

	/**
	 * Return query filtered by given search term searching the mark form property type
	 *
	 * @param string $term
	 *
	 * @return \Cake\ORM\Query
	 */
	public function filter( string $term ) {
		$query = $this->find()
		              ->distinct( 'Marks.id' )
		              ->contain( [
			              'Trees',
			              'Varieties',
			              'Varieties.Batches',
			              'Batches',
			              'MarkValues',
			              'Batches.Crossings' => [ 'joinType' => 'LEFT' ],
			              'MarkValues.MarkFormProperties',
		              ] );
		if ( $term ) {
			$query->matching( 'MarkValues.MarkFormProperties', function ( $q ) use ( $term ) {
				return $q->where( [ 'mark_form_property_type_id' => (int) $term ] );
			} );
		}

		return $query;
	}
}
