<?php

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MotherTreesView Model
 *
 * @property \Cake\ORM\Association\BelongsTo $TreesView
 * @property \Cake\ORM\Association\BelongsTo $CrossingsView
 *
 * @method \App\Model\Entity\MotherTreesView get( $primaryKey, $options = [] )
 * @method \App\Model\Entity\MotherTreesView newEntity( $data = null, array $options = [] )
 * @method \App\Model\Entity\MotherTreesView[] newEntities( array $data, array $options = [] )
 * @method \App\Model\Entity\MotherTreesView|bool save( \Cake\Datasource\EntityInterface $entity, $options = [] )
 * @method \App\Model\Entity\MotherTreesView patchEntity( \Cake\Datasource\EntityInterface $entity, array $data, array $options = [] )
 * @method \App\Model\Entity\MotherTreesView[] patchEntities( $entities, array $data, array $options = [] )
 * @method \App\Model\Entity\MotherTreesView findOrCreate( $search, callable $callback = null, $options = [] )
 */
class MotherTreesViewTable extends Table {
	/**
	 * boolean fields
	 */
	private $boolean = [ 'planed' ];

	/**
	 * select fields
	 */
	private $select = [ 'row', 'experiment_site' ];

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

		$this->setTable( 'mother_trees_view' );
		$this->setDisplayField( 'code' );
		$this->setPrimaryKey( 'id' );

		$this->belongsTo( 'TreesView', [
			'foreignKey' => 'tree_id',
			'strategy'   => 'select'
		] );
		$this->belongsTo( 'CrossingsView', [
			'foreignKey' => 'crossing_id',
			'joinType'   => 'INNER',
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
			->requirePresence( 'crossing', 'create' )
			->notEmptyString( 'crossing' );

		$validator
			->requirePresence( 'code', 'create' )
			->notEmptyString( 'code' );

		$validator
			->boolean( 'planed' )
			->requirePresence( 'planed', 'create' )
			->notEmptyString( 'planed' );

		$validator
			->date( 'date_pollen_harvested' )
			->allowEmptyDate( 'date_pollen_harvested' );

		$validator
			->date( 'date_impregnated' )
			->allowEmptyDate( 'date_impregnated' );

		$validator
			->date( 'date_fruit_harvested' )
			->allowEmptyDate( 'date_fruit_harvested' );

		$validator
			->integer( 'numb_portions' )
			->allowEmptyString( 'numb_portions' );

		$validator
			->integer( 'numb_flowers' )
			->allowEmptyString( 'numb_flowers' );

        $validator
            ->integer( 'numb_fruits' )
            ->allowEmptyString( 'numb_fruits' );

		$validator
			->integer( 'numb_seeds' )
			->allowEmptyString( 'numb_seeds' );

		$validator
			->allowEmptyString( 'note' );

		$validator
			->requirePresence( 'publicid', 'create' )
			->notEmptyString( 'publicid' );

		$validator
			->numeric( 'offset' )
			->allowEmptyString( 'offset' );

		$validator
			->allowEmptyString( 'row' );

		$validator
			->requirePresence( 'experiment_site', 'create' )
			->notEmptyString( 'experiment_site' );

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
		$rules->add( $rules->existsIn( [ 'tree_id' ], 'Trees' ) );
		$rules->add( $rules->existsIn( [ 'crossing_id' ], 'Crossings' ) );

		return $rules;
	}
}
