<?php

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use SoftDelete\Model\Table\SoftDeleteTrait;
use App\Model\Rule\IsNotReferredBy;


/**
 * Batches Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Crossings
 * @property \Cake\ORM\Association\HasMany $Marks
 * @property \Cake\ORM\Association\HasMany $Varieties
 *
 * @method \App\Model\Entity\Batch get( $primaryKey, $options = [] )
 * @method \App\Model\Entity\Batch newEntity( $data = null, array $options = [] )
 * @method \App\Model\Entity\Batch[] newEntities( array $data, array $options = [] )
 * @method \App\Model\Entity\Batch|bool save( \Cake\Datasource\EntityInterface $entity, $options = [] )
 * @method \App\Model\Entity\Batch patchEntity( \Cake\Datasource\EntityInterface $entity, array $data, array $options = [] )
 * @method \App\Model\Entity\Batch[] patchEntities( $entities, array $data, array $options = [] )
 * @method \App\Model\Entity\Batch findOrCreate( $search, callable $callback = null, $options = [] )
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class BatchesTable extends Table {
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

		$this->setTable( 'batches' );
		$this->setDisplayField( 'code' );
		$this->setPrimaryKey( 'id' );

		$this->addBehavior( 'Timestamp' );
		$this->addBehavior( 'Printable' );

		$this->belongsTo( 'Crossings', [
			'foreignKey' => 'crossing_id',
			'joinType'   => 'INNER'
		] );
		$this->hasMany( 'Marks', [
			'foreignKey' => 'batch_id'
		] );
		$this->hasMany( 'Varieties', [
			'foreignKey' => 'batch_id'
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
			->notEmptyString( 'code' )
			->add( 'code', 'custom', [
				'rule'    => function ( $value, $context ) {
					return (bool) preg_match( '/^(\d{2}[A-Z]|000)$/', $value );
				},
				'message' => __( 'Input not valid. The code must match the following pattern: "NumberNumberUppercaseletter". Example: 17A' ),
			] );

		$validator
			->localizedTime( 'date_sowed', 'date' )
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
			->localizedTime( 'date_planted', 'date' )
			->allowEmptyString( 'date_planted' );

		$validator
			->integer( 'numb_sprouts_planted' )
			->allowEmptyString( 'numb_sprouts_planted' );

		$validator
			->allowEmptyString( 'patch' );

		$validator
			->allowEmptyString( 'note' );

		$validator
			->integer( 'crossing_id' )
			->notEmptyString( 'crossing_id' );

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
		$rules->add( $rules->isUnique(
			[ 'code', 'crossing_id' ],
			__( 'A batch with this code and this crossing already exists.' )
		) );
		$rules->add( $rules->existsIn( [ 'crossing_id' ], 'Crossings' ) );

		$rules->addDelete( new IsNotReferredBy( [ 'Varieties' => 'batch_id' ] ), 'isNotReferredBy' );
		$rules->addDelete( new IsNotReferredBy( [ 'Marks' => 'batch_id' ] ), 'isNotReferredBy' );

		return $rules;
	}

    /**
     * Custom Finder Method that hides the official variety "batch" (SORTE.000)
     *
     * @param Query $query
     * @param array $options
     *
     * @return Query
     */
    public function findWithoutOfficialVarieties( Query $query, array $options ) {
        return $query->where(['Batches.id !=' => 1]);
    }

	/**
	 * Return query filtered by given search term searching the convar
	 *
	 * @param string $term
	 *
	 * @return \Cake\ORM\Query
	 */
	public function filterCrossingBatches( string $term ) {
		$list = $this->searchCrossingBatchs( $term )->toArray();
		$ids  = array_keys( $list );

		// if nothing was found
		if ( empty( $ids ) ) {
			return null;
		}

		return $this->find()
		            ->where( [ 'Batches.id IN' => $ids ] );
	}

	/**
	 * Return list with the id of the batch as key and crossing_code.batches_code as value
	 * filtered by the given search term
	 *
	 * @param string $term
	 *
	 * @return \Cake\ORM\Query
	 */
	public function searchCrossingBatchs( string $term ) {
        $query = $this->find( 'list' )
            ->select(['id', 'code' => 'crossing_batch']);

        $search = explode( '.', trim( $term ) );

        if ( ! empty( $search[0] ) ) {
            $query->where( [ 'crossing_batch LIKE' => '%' . $search[0] . '%.%' ] );
        }

        if ( 1 < count( $search ) && ! empty( $search[1] ) ) {
            $query->andWhere( [ 'crossing_batch LIKE' => '%.%' . $search[1] . '%' ] );
        }

		return $query;
	}

	public function getCrossingBatchList( int $id ) {
		$batch = $this->get( $id );

		return [ $id => $batch->crossing_batch ];
	}

	/**
	 * Return label to print in Zebra Printing Language
	 *
	 * @param int $id
	 *
	 * @return string
	 */
	public function getLabelZpl( int $id ) {
		$batch       = $this->get( $id );
		$description = $batch->crossing_batch;

		return $this->getZPL( [$description] );

	}
}
