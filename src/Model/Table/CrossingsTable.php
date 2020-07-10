<?php

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use SoftDelete\Model\Table\SoftDeleteTrait;
use App\Model\Rule\IsNotReferredBy;


/**
 * Crossings Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Varieties
 * @property \Cake\ORM\Association\HasMany $MotherTrees
 * @property \Cake\ORM\Association\HasMany $Batches
 *
 * @method \App\Model\Entity\Crossing get( $primaryKey, $options = [] )
 * @method \App\Model\Entity\Crossing newEntity( $data = null, array $options = [] )
 * @method \App\Model\Entity\Crossing[] newEntities( array $data, array $options = [] )
 * @method \App\Model\Entity\Crossing|bool save( \Cake\Datasource\EntityInterface $entity, $options = [] )
 * @method \App\Model\Entity\Crossing patchEntity( \Cake\Datasource\EntityInterface $entity, array $data, array $options = [] )
 * @method \App\Model\Entity\Crossing[] patchEntities( $entities, array $data, array $options = [] )
 * @method \App\Model\Entity\Crossing findOrCreate( $search, callable $callback = null, $options = [] )
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CrossingsTable extends Table {
	use SoftDeleteTrait;

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 *
	 * @return void
	 */
	public function initialize( array $config ) {
		parent::initialize( $config );

		$this->setTable( 'crossings' );
		$this->setDisplayField( 'code' );
		$this->setPrimaryKey( 'id' );

		$this->addBehavior( 'Timestamp' );

		$this->belongsTo( 'Varieties', [
			'foreignKey' => 'mother_variety_id'
		] );
		$this->belongsTo( 'Varieties', [
			'foreignKey' => 'father_variety_id'
		] );
		$this->hasMany( 'Batches', [
			'foreignKey' => 'crossing_id'
		] );
		$this->hasMany( 'MotherTrees', [
			'foreignKey' => 'crossing_id'
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
			->allowEmptyString( 'id', __('This field is required'), 'create' )
			->add( 'id', 'unique', [ 'rule' => 'validateUnique', 'provider' => 'table' ] );

		$validator
			->requirePresence( 'code', 'create' )
			->notEmptyString( 'code' )
			->add( 'code', 'custom', [
				'rule'    => function ( $value, $context ) {
					return (bool) preg_match( '/^[a-zA-Z0-9]{4,8}$/', $value );
				},
				'message' => __( 'Input not valid. The code must only contain alphanumerical characters (without umlauts). Length between four and eight characters.' ),
			] );

        $validator
            ->allowEmptyString( 'target' );

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
		$rules->add( $rules->isUnique( [ 'code' ],
			__( 'This code has already been used. Please use a unique code.' ) ) );
		$rules->add( $rules->existsIn( [ 'mother_variety_id' ], 'Varieties' ) );
		$rules->add( $rules->existsIn( [ 'father_variety_id' ], 'Varieties' ) );
		$rules->add( $rules->isUnique(
			[ 'mother_variety_id', 'father_variety_id' ],
			__( 'A crossing with the same mother variety and the same father variety already exists.' )
		) );

		$rules->addDelete( new IsNotReferredBy( [ 'Batches' => 'crossing_id' ] ), 'isNotReferredBy' );
		$rules->addDelete( new IsNotReferredBy( [ 'MotherTrees' => 'crossing_id' ] ), 'isNotReferredBy' );

		return $rules;
	}

	/**
	 * Return query filtered by given search term searching the code
	 *
	 * @param string $term
	 *
	 * @return \Cake\ORM\Query
	 */
	public function filterCodes( string $term ) {

		return $this->find()
		            ->where( [ 'code LIKE' => $term . '%' ] );
	}
}
