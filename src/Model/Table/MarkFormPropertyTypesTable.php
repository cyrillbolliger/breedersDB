<?php

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use App\Model\Rule\IsNotReferredBy;


/**
 * MarkFormPropertyTypes Model
 *
 * @property \Cake\ORM\Association\HasMany $MarkFormProperties
 *
 * @method \App\Model\Entity\MarkFormPropertyType get( $primaryKey, $options = [] )
 * @method \App\Model\Entity\MarkFormPropertyType newEntity( $data = null, array $options = [] )
 * @method \App\Model\Entity\MarkFormPropertyType[] newEntities( array $data, array $options = [] )
 * @method \App\Model\Entity\MarkFormPropertyType|bool save( \Cake\Datasource\EntityInterface $entity, $options = [] )
 * @method \App\Model\Entity\MarkFormPropertyType patchEntity( \Cake\Datasource\EntityInterface $entity, array $data, array $options = [] )
 * @method \App\Model\Entity\MarkFormPropertyType[] patchEntities( $entities, array $data, array $options = [] )
 * @method \App\Model\Entity\MarkFormPropertyType findOrCreate( $search, callable $callback = null, $options = [] )
 */
class MarkFormPropertyTypesTable extends Table {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 *
	 * @return void
	 */
	public function initialize( array $config ): void {
		parent::initialize( $config );

		$this->setTable( 'mark_form_property_types' );
		$this->setDisplayField( 'name' );
		$this->setPrimaryKey( 'id' );

		$this->hasMany( 'MarkFormProperties', [
			'foreignKey' => 'mark_form_property_type_id'
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
			->allowEmptyString( 'id', __('This field is required'), 'create' )
			->add( 'id', 'unique', [ 'rule' => 'validateUnique', 'provider' => 'table' ] );

		$validator
			->requirePresence( 'name', 'create' )
			->notEmptyString( 'name' );

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
		$rules->add( $rules->isUnique( [ 'name' ],
			__( 'This name has already been used. Please use a unique name.' ) ) );

		$rules->addDelete( new IsNotReferredBy( [ 'MarkFormProperties' => 'mark_form_property_type_id' ] ),
			'isNotReferredBy' );

		return $rules;
	}
}
