<?php

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use App\Model\Rule\IsNotReferredBy;


/**
 * ExperimentSites Model
 *
 * @property \Cake\ORM\Association\HasMany $Trees
 *
 * @method \App\Model\Entity\ExperimentSite get( $primaryKey, $options = [] )
 * @method \App\Model\Entity\ExperimentSite newEntity( $data = null, array $options = [] )
 * @method \App\Model\Entity\ExperimentSite[] newEntities( array $data, array $options = [] )
 * @method \App\Model\Entity\ExperimentSite|bool save( \Cake\Datasource\EntityInterface $entity, $options = [] )
 * @method \App\Model\Entity\ExperimentSite patchEntity( \Cake\Datasource\EntityInterface $entity, array $data, array $options = [] )
 * @method \App\Model\Entity\ExperimentSite[] patchEntities( $entities, array $data, array $options = [] )
 * @method \App\Model\Entity\ExperimentSite findOrCreate( $search, callable $callback = null, $options = [] )
 */
class ExperimentSitesTable extends Table {
	
	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 *
	 * @return void
	 */
	public function initialize( array $config ) {
		parent::initialize( $config );
		
		$this->table( 'experiment_sites' );
		$this->displayField( 'name' );
		$this->primaryKey( 'id' );
		
		$this->hasMany( 'Trees', [
			'foreignKey' => 'experiment_site_id'
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
			->requirePresence( 'name', 'create' )
			->notEmpty( 'name' );
		
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
		$rules->add( $rules->isUnique( [ 'name' ],
			__( 'This name has already been used. Please use a unique name.' ) ) );
		
		$rules->addDelete( new IsNotReferredBy( [ 'Trees' => 'experiment_site_id' ] ), 'isNotReferredBy' );
		
		return $rules;
	}
}
