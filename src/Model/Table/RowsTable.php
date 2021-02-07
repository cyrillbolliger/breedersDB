<?php

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use SoftDelete\Model\Table\SoftDeleteTrait;
use App\Model\Rule\IsNotReferredBy;

/**
 * Rows Model
 *
 * @property \Cake\ORM\Association\HasMany $Trees
 *
 * @method \App\Model\Entity\Row get( $primaryKey, $options = [] )
 * @method \App\Model\Entity\Row newEntity( $data = null, array $options = [] )
 * @method \App\Model\Entity\Row[] newEntities( array $data, array $options = [] )
 * @method \App\Model\Entity\Row|bool save( \Cake\Datasource\EntityInterface $entity, $options = [] )
 * @method \App\Model\Entity\Row patchEntity( \Cake\Datasource\EntityInterface $entity, array $data, array $options = [] )
 * @method \App\Model\Entity\Row[] patchEntities( $entities, array $data, array $options = [] )
 * @method \App\Model\Entity\Row findOrCreate( $search, callable $callback = null, $options = [] )
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RowsTable extends Table {
	use SoftDeleteTrait;

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 *
	 * @return void
	 */
	public function initialize( array $config ): void {
		parent::initialize( $config );

		$this->setTable( 'rows' );
		$this->setDisplayField( 'code' );
		$this->setPrimaryKey( 'id' );

		$this->addBehavior( 'Timestamp' );

		$this->hasMany( 'Trees', [
			'foreignKey' => 'row_id'
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
			->requirePresence( 'code', 'create' )
			->notEmptyString( 'code' );

		$validator
			->localizedTime( 'date_created', 'date' )
			->allowEmptyDate( 'date_created' );

		$validator
			->localizedTime( 'date_eliminated', 'date' )
			->allowEmptyDate( 'date_eliminated' );

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
	public function buildRules( RulesChecker $rules ): \Cake\ORM\RulesChecker {
		$rules->add( $rules->isUnique( [ 'id' ] ) );
		$rules->add( $rules->isUnique( [ 'code' ],
			__( 'This code has already been used. Please use a unique code.' ) ) );

		$rules->addDelete( new IsNotReferredBy( [ 'Trees' => 'row_id' ] ), 'isNotReferredBy' );

		return $rules;
	}

	/**
	 * Return query filtered by given search term searching the code
	 *
	 * @param string $term
	 *
	 * @return \Cake\ORM\Query
	 */
	public function filter( string $term ) {

		$query = $this->find()
		              ->where( [ 'code LIKE' => '%' . $term . '%' ] );

		return $query;
	}
}
