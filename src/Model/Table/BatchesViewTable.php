<?php

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * BatchesView Model
 *
 * @property \Cake\ORM\Association\BelongsTo $CrossingsView
 * @property \Cake\ORM\Association\HasMany $MarksView
 * @property \Cake\ORM\Association\HasMany $VarietiesView
 *
 * @method \App\Model\Entity\BatchesView get( $primaryKey, $options = [] )
 * @method \App\Model\Entity\BatchesView newEntity( $data = null, array $options = [] )
 * @method \App\Model\Entity\BatchesView[] newEntities( array $data, array $options = [] )
 * @method \App\Model\Entity\BatchesView|bool save( \Cake\Datasource\EntityInterface $entity, $options = [] )
 * @method \App\Model\Entity\BatchesView patchEntity( \Cake\Datasource\EntityInterface $entity, array $data, array $options = [] )
 * @method \App\Model\Entity\BatchesView[] patchEntities( $entities, array $data, array $options = [] )
 * @method \App\Model\Entity\BatchesView findOrCreate( $search, callable $callback = null, $options = [] )
 */
class BatchesViewTable extends Table {
	/**
	 * boolean fields
	 */
	private $boolean = [];

	/**
	 * select fields
	 */
	private $select = [];

	/**
	 * @return mixed
	 */
	public function getBooleanFields() {
		return $this->boolean;
	}

	/**
	 * @return mixed
	 */
	public function getSelectFields() {
		return $this->select;
	}

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 *
	 * @return void
	 */
	public function initialize( array $config ) {
		parent::initialize( $config );

		$this->setTable( 'batches_view' );
		$this->setDisplayField( 'code' );
		$this->setPrimaryKey( 'id' );

		$this->belongsTo( 'CrossingsView', [
			'foreignKey' => 'crossing_id',
			'joinType'   => 'INNER',
			'strategy'   => 'select'
		] );
		$this->hasMany( 'MarksView', [
			'foreignKey' => 'batch_id',
			'strategy'   => 'select'
		] );
		$this->hasMany( 'VarietiesView', [
			'foreignKey' => 'batch_id',
			'strategy'   => 'select'
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
			->requirePresence( 'id', 'create' )
			->notEmptyString( 'id' );

		$validator
			->requirePresence( 'crossing_batch', 'create' )
			->notEmptyString( 'crossing_batch' );

		$validator
			->date( 'date_sowed' )
			->allowEmptyDate( 'date_sowed' );

		$validator
			->integer( 'numb_seeds_sowed' )
			->allowEmptyString( 'numb_seeds_sowed' );

		$validator
			->integer( 'numb_sprouts_grown' )
			->allowEmptyString( 'numb_sprouts_grown' );

		$validator
			->allowEmptyString( 'seed_tray' );

		$validator
			->date( 'date_planted' )
			->allowEmptyDate( 'date_planted' );

		$validator
			->integer( 'numb_sprouts_planted' )
			->allowEmptyString( 'numb_sprouts_planted' );

		$validator
			->allowEmptyString( 'patch' );

		$validator
			->allowEmptyString( 'note' );

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
		$rules->add( $rules->existsIn( [ 'crossing_id' ], 'Crossings' ) );

		return $rules;
	}
}
