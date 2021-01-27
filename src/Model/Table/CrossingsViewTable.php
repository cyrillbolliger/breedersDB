<?php

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CrossingsView Model
 *
 * @property \Cake\ORM\Association\BelongsTo $VarietiesView
 * @property \Cake\ORM\Association\HasMany $MotherTreesView
 * @property \Cake\ORM\Association\HasMany $BatchesView
 *
 * @method \App\Model\Entity\CrossingsView get( $primaryKey, $options = [] )
 * @method \App\Model\Entity\CrossingsView newEntity( $data = null, array $options = [] )
 * @method \App\Model\Entity\CrossingsView[] newEntities( array $data, array $options = [] )
 * @method \App\Model\Entity\CrossingsView|bool save( \Cake\Datasource\EntityInterface $entity, $options = [] )
 * @method \App\Model\Entity\CrossingsView patchEntity( \Cake\Datasource\EntityInterface $entity, array $data, array $options = [] )
 * @method \App\Model\Entity\CrossingsView[] patchEntities( $entities, array $data, array $options = [] )
 * @method \App\Model\Entity\CrossingsView findOrCreate( $search, callable $callback = null, $options = [] )
 */
class CrossingsViewTable extends Table {
	/**
	 * boolean fields
	 */
	private $boolean = [];

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
	public function initialize( array $config ): void {
		parent::initialize( $config );

		$this->setTable( 'crossings_view' );
		$this->setDisplayField( 'code' );
		$this->setPrimaryKey( 'id' );

		$this->hasMany( 'BatchesView', [
			'foreignKey' => 'crossing_id',
			'strategy'   => 'select'
		] );
		$this->hasMany( 'MotherTreesView', [
			'foreignKey' => 'crossing_id',
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
	public function validationDefault( Validator $validator ): \Cake\Validation\Validator {
		$validator
			->integer( 'id' )
			->requirePresence( 'id', 'create' )
			->notEmptyString( 'id' );

		$validator
			->requirePresence( 'code', 'create' )
			->notEmptyString( 'code' );

		$validator
			->allowEmptyString( 'mother_variety' );

		$validator
			->allowEmptyString( 'father_variety' );

        $validator
            ->allowEmptyString( 'target' );

		return $validator;
	}
}
