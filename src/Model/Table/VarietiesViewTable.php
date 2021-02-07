<?php

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * VarietiesView Model
 *
 * @property \Cake\ORM\Association\BelongsTo $BatchesView
 * @property \Cake\ORM\Association\HasMany $ScionsBundlesView
 * @property \Cake\ORM\Association\HasMany $TreesView
 *
 * @method \App\Model\Entity\VarietiesView get( $primaryKey, $options = [] )
 * @method \App\Model\Entity\VarietiesView newEntity( $data = null, array $options = [] )
 * @method \App\Model\Entity\VarietiesView[] newEntities( array $data, array $options = [] )
 * @method \App\Model\Entity\VarietiesView|bool save( \Cake\Datasource\EntityInterface $entity, $options = [] )
 * @method \App\Model\Entity\VarietiesView patchEntity( \Cake\Datasource\EntityInterface $entity, array $data, array $options = [] )
 * @method \App\Model\Entity\VarietiesView[] patchEntities( $entities, array $data, array $options = [] )
 * @method \App\Model\Entity\VarietiesView findOrCreate( $search, callable $callback = null, $options = [] )
 */
class VarietiesViewTable extends Table {
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
	public function initialize( array $config ): void {
		parent::initialize( $config );

		$this->setTable( 'varieties_view' );

		$this->setDisplayField( 'code' );
		$this->setPrimaryKey( 'id' );

		$this->belongsTo( 'BatchesView', [
			'foreignKey' => 'batch_id',
			'joinType'   => 'INNER',
			'strategy'   => 'select'
		] );
		$this->hasMany( 'ScionsBundlesView', [
			'foreignKey' => 'variety_id',
			'strategy'   => 'select'
		] );
		$this->hasMany( 'TreesView', [
			'foreignKey' => 'variety_id',
			'strategy'   => 'select'
		] );
		$this->hasMany( 'MarksView', [
			'foreignKey' => 'variety_id',
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
	public function validationDefault( Validator $validator ): \Cake\Validation\Validator {
		$validator
			->integer( 'id' )
			->requirePresence( 'id', 'create' )
			->notEmptyString( 'id' );

		$validator
			->requirePresence( 'convar', 'create' )
			->notEmptyString( 'convar' );

		$validator
			->allowEmptyString( 'official_name' );

		$validator
			->allowEmptyString( 'acronym' );

		$validator
			->allowEmptyString( 'plant_breeder' );

		$validator
			->allowEmptyString( 'registration' );

		$validator
			->allowEmptyString( 'description' );

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
		$rules->add( $rules->existsIn( [ 'batch_id' ], 'Batches' ) );

		return $rules;
	}
}
