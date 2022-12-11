<?php

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use App\Model\Rule\IsNotReferredBy;
use SoftDelete\Model\Table\SoftDeleteTrait;

/**
 * QueryGroups Model
 *
 * @property \Cake\ORM\Association\HasMany $Queries
 *
 * @method \App\Model\Entity\QueryGroup get( $primaryKey, $options = [] )
 * @method \App\Model\Entity\QueryGroup newEntity( $data = null, array $options = [] )
 * @method \App\Model\Entity\QueryGroup[] newEntities( array $data, array $options = [] )
 * @method \App\Model\Entity\QueryGroup|bool save( \Cake\Datasource\EntityInterface $entity, $options = [] )
 * @method \App\Model\Entity\QueryGroup patchEntity( \Cake\Datasource\EntityInterface $entity, array $data, array $options = [] )
 * @method \App\Model\Entity\QueryGroup[] patchEntities( $entities, array $data, array $options = [] )
 * @method \App\Model\Entity\QueryGroup findOrCreate( $search, callable $callback = null, $options = [] )
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class QueryGroupsTable extends Table {

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

		$this->setTable( 'query_groups' );
		$this->setDisplayField( 'code' );
		$this->setPrimaryKey( 'id' );

		$this->addBehavior( 'Timestamp' );

		$this->hasMany( 'Queries', [
			'foreignKey' => 'query_group_id'
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
			->add( 'code', 'unique', [ 'rule' => 'validateUnique', 'provider' => 'table' ] );

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
		$rules->add( $rules->isUnique( [ 'code' ] ) );

		$rules->addDelete( new IsNotReferredBy( [ 'Queries' => 'query_group_id' ] ), 'isNotReferredBy' );

		return $rules;
	}

    public function findVersionNull(Query $query, array $options): Query
    {
        return $query->find( 'all' )
            ->where(['version IS' => null])
            ->contain( 'Queries' )
            ->order( 'code' );
    }

    public function findVersionNullList(Query $query, array $options): Query
    {
        return $query->find( 'list' )
            ->where(['version IS' => null])
            ->order( 'code' );
    }

    public function findVersion1(Query $query, array $options): Query
    {
        return $query->find('all')
            ->where([
                        'OR' => [
                            ['version LIKE' => '1.%'],
                            ['version' => '1']
                        ]
                    ]);
    }
}
