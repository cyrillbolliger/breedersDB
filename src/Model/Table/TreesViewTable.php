<?php

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * TreesView Model
 *
 * @property \Cake\ORM\Association\BelongsTo $VarietiesView
 * @property \Cake\ORM\Association\HasMany $MarksView
 * @property \Cake\ORM\Association\HasMany $MotherTreesView
 *
 * @method \App\Model\Entity\TreesView get( $primaryKey, $options = [] )
 * @method \App\Model\Entity\TreesView newEntity( $data = null, array $options = [] )
 * @method \App\Model\Entity\TreesView[] newEntities( array $data, array $options = [] )
 * @method \App\Model\Entity\TreesView|bool save( \Cake\Datasource\EntityInterface $entity, $options = [] )
 * @method \App\Model\Entity\TreesView patchEntity( \Cake\Datasource\EntityInterface $entity, array $data, array $options = [] )
 * @method \App\Model\Entity\TreesView[] patchEntities( $entities, array $data, array $options = [] )
 * @method \App\Model\Entity\TreesView findOrCreate( $search, callable $callback = null, $options = [] )
 */
class TreesViewTable extends Table {
	/**
	 * boolean fields
	 */
	private $boolean = [ 'genuine_seedling' ];
	
	/**
	 * select fields
	 */
	private $select = [ 'row', 'grafting', 'rootstock', 'experiment_site' ];
	
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
		
		$this->table( 'trees_view' );
		$this->displayField( 'publicid' );
		$this->primaryKey( 'id' );
		
		$this->belongsTo( 'VarietiesView', [
			'foreignKey' => 'variety_id',
			'joinType'   => 'INNER',
			'strategy'   => 'select'
		] );
		$this->hasMany( 'MarksView', [
			'foreignKey' => 'tree_id',
			'strategy'   => 'select'
		] );
		$this->hasMany( 'MotherTreesView', [
			'foreignKey' => 'tree_id',
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
			->notEmpty( 'id' );
		
		$validator
			->requirePresence( 'publicid', 'create' )
			->notEmpty( 'publicid' );
		
		$validator
			->requirePresence( 'convar', 'create' )
			->notEmpty( 'convar' );
		
		$validator
			->date( 'date_grafted' )
			->allowEmpty( 'date_grafted' );
		
		$validator
			->date( 'date_planted' )
			->allowEmpty( 'date_planted' );
		
		$validator
			->date( 'date_eliminated' )
			->allowEmpty( 'date_eliminated' );
		
		$validator
			->boolean( 'genuine_seedling' )
			->requirePresence( 'genuine_seedling', 'create' )
			->notEmpty( 'genuine_seedling' );
		
		$validator
			->numeric( 'offset' )
			->allowEmpty( 'offset' );
		
		$validator
			->allowEmpty( 'row' );
		
		$validator
			->allowEmpty( 'note' );
		
		$validator
			->allowEmpty( 'grafting' );
		
		$validator
			->allowEmpty( 'rootstock' );
		
		$validator
			->requirePresence( 'experiment_site', 'create' )
			->notEmpty( 'experiment_site' );
		
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
		$rules->add( $rules->existsIn( [ 'variety_id' ], 'Varieties' ) );
		
		return $rules;
	}
}
