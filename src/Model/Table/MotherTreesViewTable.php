<?php

namespace App\Model\Table;

use App\Model\Behavior\FilterSchemaBehavior;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MotherTreesView Model
 *
 * @property \Cake\ORM\Association\BelongsTo $TreesView
 * @property \Cake\ORM\Association\BelongsTo $CrossingsView
 *
 * @method \App\Model\Entity\MotherTreesView get( $primaryKey, $options = [] )
 * @method \App\Model\Entity\MotherTreesView newEntity( $data = null, array $options = [] )
 * @method \App\Model\Entity\MotherTreesView[] newEntities( array $data, array $options = [] )
 * @method \App\Model\Entity\MotherTreesView|bool save( \Cake\Datasource\EntityInterface $entity, $options = [] )
 * @method \App\Model\Entity\MotherTreesView patchEntity( \Cake\Datasource\EntityInterface $entity, array $data, array $options = [] )
 * @method \App\Model\Entity\MotherTreesView[] patchEntities( $entities, array $data, array $options = [] )
 * @method \App\Model\Entity\MotherTreesView findOrCreate( $search, callable $callback = null, $options = [] )
 *
 * @mixin FilterSchemaBehavior
 */
class MotherTreesViewTable extends Table {
	/**
	 * boolean fields
	 */
	private $boolean = [ 'planed' ];

	/**
	 * select fields
	 */
	private $select = [ 'row', 'experiment_site' ];

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

    public function getTranslatedName(): string {
        return __('Mother Trees');
    }

    public function getTranslatedColumnName(string $column): ?string {
        $columns = [
            'id'                       => __( 'Id' ),
            'crossing'                 => __( 'Crossing' ),
            'code'                     => __( 'Identification' ),
            'planed'                   => __( 'Planed' ),
            'date_pollen_harvested'    => __( 'Date Pollen Harvested' ),
            'date_impregnated'         => __( 'Date Impregnated' ),
            'date_fruit_harvested'     => __( 'Date Fruit Harvested' ),
            'numb_portions'            => __( 'Numb Portions' ),
            'numb_flowers'             => __( 'Numb Flowers' ),
            'numb_fruits'              => __( 'Numb Fruits' ),
            'numb_seeds'               => __( 'Numb Seeds' ),
            'note'                     => __( 'Note' ),
            'convar'                   => __( 'Convar' ),
            'publicid'                 => __( 'Publicid' ),
            'offset'                   => __( 'Offset' ),
            'row'                      => __( 'Row' ),
            'experiment_site'          => __( 'Experiment Site' ),
            'tree_id'                  => __( 'Tree Id' ),
            'crossing_id'              => __( 'Crossing Id' ),
        ];

        return $columns[$column] ?? null;
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

		$this->setTable( 'mother_trees_view' );
		$this->setDisplayField( 'code' );
		$this->setPrimaryKey( 'id' );

        $this->addBehavior( 'FilterSchema' );

		$this->belongsTo( 'TreesView', [
			'foreignKey' => 'tree_id',
			'strategy'   => 'select'
		] );
		$this->belongsTo( 'CrossingsView', [
			'foreignKey' => 'crossing_id',
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
	public function validationDefault( Validator $validator ): \Cake\Validation\Validator {
		$validator
			->integer( 'id' )
			->requirePresence( 'id', 'create' )
			->notEmptyString( 'id' );

		$validator
			->requirePresence( 'crossing', 'create' )
			->notEmptyString( 'crossing' );

		$validator
			->requirePresence( 'code', 'create' )
			->notEmptyString( 'code' );

		$validator
			->boolean( 'planed' )
			->requirePresence( 'planed', 'create' )
			->notEmptyString( 'planed' );

		$validator
			->date( 'date_pollen_harvested' )
			->allowEmptyDate( 'date_pollen_harvested' );

		$validator
			->date( 'date_impregnated' )
			->allowEmptyDate( 'date_impregnated' );

		$validator
			->date( 'date_fruit_harvested' )
			->allowEmptyDate( 'date_fruit_harvested' );

		$validator
			->integer( 'numb_portions' )
			->allowEmptyString( 'numb_portions' );

		$validator
			->integer( 'numb_flowers' )
			->allowEmptyString( 'numb_flowers' );

        $validator
            ->integer( 'numb_fruits' )
            ->allowEmptyString( 'numb_fruits' );

		$validator
			->integer( 'numb_seeds' )
			->allowEmptyString( 'numb_seeds' );

		$validator
			->allowEmptyString( 'note' );

		$validator
			->requirePresence( 'publicid', 'create' )
			->notEmptyString( 'publicid' );

		$validator
			->numeric( 'offset' )
			->allowEmptyString( 'offset' );

		$validator
			->allowEmptyString( 'row' );

		$validator
			->requirePresence( 'experiment_site', 'create' )
			->notEmptyString( 'experiment_site' );

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
		$rules->add( $rules->existsIn( [ 'tree_id' ], 'Trees' ) );
		$rules->add( $rules->existsIn( [ 'crossing_id' ], 'Crossings' ) );

		return $rules;
	}
}
