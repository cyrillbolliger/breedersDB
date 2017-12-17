<?php

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use App\Model\Rule\IsNotReferredBy;


/**
 * Graftings Model
 *
 * @property \Cake\ORM\Association\HasMany $Trees
 *
 * @method \App\Model\Entity\Grafting get( $primaryKey, $options = [] )
 * @method \App\Model\Entity\Grafting newEntity( $data = null, array $options = [] )
 * @method \App\Model\Entity\Grafting[] newEntities( array $data, array $options = [] )
 * @method \App\Model\Entity\Grafting|bool save( \Cake\Datasource\EntityInterface $entity, $options = [] )
 * @method \App\Model\Entity\Grafting patchEntity( \Cake\Datasource\EntityInterface $entity, array $data, array $options = [] )
 * @method \App\Model\Entity\Grafting[] patchEntities( $entities, array $data, array $options = [] )
 * @method \App\Model\Entity\Grafting findOrCreate( $search, callable $callback = null, $options = [] )
 */
class GraftingsTable extends Table {
	
	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 *
	 * @return void
	 */
	public function initialize( array $config ) {
		parent::initialize( $config );
		
		$this->table( 'graftings' );
		$this->displayField( 'name' );
		$this->primaryKey( 'id' );
		
		$this->hasMany( 'Trees', [
			'foreignKey' => 'grafting_id'
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
		
		$rules->addDelete( new IsNotReferredBy( [ 'Trees' => 'grafting_id' ] ), 'isNotReferredBy' );
		
		return $rules;
	}
}
