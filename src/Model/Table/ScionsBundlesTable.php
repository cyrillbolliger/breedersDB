<?php

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use SoftDelete\Model\Table\SoftDeleteTrait;

/**
 * ScionsBundles Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Varieties
 *
 * @method \App\Model\Entity\ScionsBundle get( $primaryKey, $options = [] )
 * @method \App\Model\Entity\ScionsBundle newEntity( $data = null, array $options = [] )
 * @method \App\Model\Entity\ScionsBundle[] newEntities( array $data, array $options = [] )
 * @method \App\Model\Entity\ScionsBundle|bool save( \Cake\Datasource\EntityInterface $entity, $options = [] )
 * @method \App\Model\Entity\ScionsBundle patchEntity( \Cake\Datasource\EntityInterface $entity, array $data, array $options = [] )
 * @method \App\Model\Entity\ScionsBundle[] patchEntities( $entities, array $data, array $options = [] )
 * @method \App\Model\Entity\ScionsBundle findOrCreate( $search, callable $callback = null, $options = [] )
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ScionsBundlesTable extends Table {
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

		$this->setTable( 'scions_bundles' );
		$this->setDisplayField( 'code' );
		$this->setPrimaryKey( 'id' );

		$this->addBehavior( 'Timestamp' );

		$this->belongsTo( 'Varieties', [
			'foreignKey' => 'variety_id',
			'joinType'   => 'INNER'
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
			->allowEmptyString( 'id', 'create' )
			->add( 'id', 'unique', [ 'rule' => 'validateUnique', 'provider' => 'table' ] );

		$validator
			->requirePresence( 'code', 'create' )
			->notEmptyString( 'code' );

		$validator
			->integer( 'numb_scions' )
			->allowEmptyString( 'numb_scions' );

		$validator
			->localizedTime( 'date_scions_harvest', 'date' )
			->allowEmptyDate( 'date_scions_harvest' );

		$validator
			->allowEmptyString( 'descents_publicid_list' );

		$validator
			->allowEmptyString( 'note' );

		$validator
			->boolean( 'external_use' )
			->requirePresence( 'external_use', 'create' )
			->notEmptyString( 'external_use' );

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
		$rules->add( $rules->existsIn( [ 'variety_id' ], 'Varieties' ) );

		return $rules;
	}

	/**
	 * Return query filtered by given search term searching the convar and code
	 *
	 * @param string $term
	 *
	 * @return \Cake\ORM\Query
	 */
	public function filter( string $term ) {

		$varieties   = $this->Varieties->searchConvars( $term )->toArray();
		$variety_ids = array_keys( $varieties );

		$where[] = [ 'ScionsBundles.code LIKE' => '%' . $term . '%' ];

		if ( ! empty( $variety_ids ) ) {
			$where[] = [ 'variety_id IN' => $variety_ids ];
		}

		// if nothing was found
		if ( empty( $where ) ) {
			return null;
		}

        if ( 2 == count( $where ) ) {
            $condition = ['OR' => [$where[0], $where[1]]];
        } else {
            $condition = $where[0];
        }

        return $this->find()
                      ->contain( [ 'Varieties' ] )
                      ->where( $condition );
	}
}
