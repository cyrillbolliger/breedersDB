<?php

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ScionsBundlesView Model
 *
 * @property \Cake\ORM\Association\BelongsTo $VarietiesView
 *
 * @method \App\Model\Entity\ScionsBundlesView get( $primaryKey, $options = [] )
 * @method \App\Model\Entity\ScionsBundlesView newEntity( $data = null, array $options = [] )
 * @method \App\Model\Entity\ScionsBundlesView[] newEntities( array $data, array $options = [] )
 * @method \App\Model\Entity\ScionsBundlesView|bool save( \Cake\Datasource\EntityInterface $entity, $options = [] )
 * @method \App\Model\Entity\ScionsBundlesView patchEntity( \Cake\Datasource\EntityInterface $entity, array $data, array $options = [] )
 * @method \App\Model\Entity\ScionsBundlesView[] patchEntities( $entities, array $data, array $options = [] )
 * @method \App\Model\Entity\ScionsBundlesView findOrCreate( $search, callable $callback = null, $options = [] )
 */
class ScionsBundlesViewTable extends Table {
	/**
	 * boolean fields
	 */
	private $boolean = [ 'external_use' ];
	
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
		
		$this->table( 'scions_bundles_view' );
		$this->displayField( 'code' );
		$this->primaryKey( 'id' );
		
		$this->belongsTo( 'VarietiesView', [
			'foreignKey' => 'variety_id',
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
			->notEmpty( 'id' );
		
		$validator
			->requirePresence( 'identification', 'create' )
			->notEmpty( 'identification' );
		
		$validator
			->requirePresence( 'convar', 'create' )
			->notEmpty( 'convar' );
		
		$validator
			->integer( 'numb_scions' )
			->allowEmpty( 'numb_scions' );
		
		$validator
			->date( 'date_scions_harvest' )
			->allowEmpty( 'date_scions_harvest' );
		
		$validator
			->allowEmpty( 'descents_publicid_list' );
		
		$validator
			->allowEmpty( 'note' );
		
		$validator
			->boolean( 'external_use' )
			->requirePresence( 'external_use', 'create' )
			->notEmpty( 'external_use' );
		
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
