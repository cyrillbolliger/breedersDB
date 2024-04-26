<?php

namespace App\Model\Table;

use App\Model\Behavior\FilterSchemaBehavior;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * TreesView Model
 *
 * @property \Cake\ORM\Association\BelongsTo $VarietiesView
 * @property \Cake\ORM\Association\HasMany $MarksView
 * @property \Cake\ORM\Association\HasMany $MotherTreesView
 *
 * @method \App\Model\Entity\TreesView get( $primaryKey, $options = [] )
 * @method \App\Model\Entity\TreesView newEntity( $data = null, array $options = [] )
 * @method \App\Model\Entity\TreesView[] newEntities( array $data, array $options = [] )
 * @method \App\Model\Entity\TreesView|bool save( \Cake\Datasource\EntityInterface $entity, $options = [] )
 * @method \App\Model\Entity\TreesView patchEntity( \Cake\Datasource\EntityInterface $entity, array $data, array $options = [] )
 * @method \App\Model\Entity\TreesView[] patchEntities( $entities, array $data, array $options = [] )
 * @method \App\Model\Entity\TreesView findOrCreate( $search, callable $callback = null, $options = [] )
 *
 * @mixin FilterSchemaBehavior
 */
class TreesViewTable extends Table {
	/**
	 * boolean fields
	 */
	private $boolean = [ 'genuine_seedling' ];

	/**
	 * select fields
	 */
	private $select = [ 'row', 'grafting', 'rootstock', 'experiment_site' ];

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
        return __('Trees');
    }

    public function getTranslatedColumnName(string $column): ?string {
        $columns = [
            'id' => __('Id'),
            'publicid' => __('Publicid'),
            'name' => __('Name'),
            'convar' => __('Convar'),
            'date_grafted' => __('Date Grafted'),
            'date_planted' => __('Date Planted'),
            'date_eliminated' => __('Date Eliminated'),
            'date_labeled' => __('Date Labeled'),
            'genuine_seedling' => __('Genuine Seedling'),
            'offset' => __('Offset'),
            'dont_eliminate' => __("Don't eliminate"),
            'row' => __('Row'),
            'note' => __('Note'),
            'variety_id' => __('Variety Id'),
            'grafting' => __('Grafting'),
            'rootstock' => __('Rootstock'),
            'experiment_site' => __('Experiment Site'),
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

		$this->setTable( 'trees_view' );
		$this->setDisplayField( 'publicid' );
		$this->setPrimaryKey( 'id' );

        $this->addBehavior( 'FilterSchema' );

		$this->belongsTo( 'VarietiesView', [
			'foreignKey' => 'variety_id',
			'joinType'   => 'INNER',
			'strategy'   => 'select'
		] );
		$this->hasMany( 'MarksView', [
			'foreignKey' => 'tree_id',
			'strategy'   => 'select'
		] );
		$this->hasMany( 'MotherTreesView', [
			'foreignKey' => 'tree_id',
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
			->requirePresence( 'publicid', 'create' )
			->notEmptyString( 'publicid' );

		$validator
			->requirePresence( 'convar', 'create' )
			->notEmptyString( 'convar' );

		$validator
			->date( 'date_grafted' )
			->allowEmptyDate( 'date_grafted' );

		$validator
			->date( 'date_planted' )
			->allowEmptyDate( 'date_planted' );

		$validator
			->date( 'date_eliminated' )
			->allowEmptyDate( 'date_eliminated' );

		$validator
			->boolean( 'genuine_seedling' )
			->requirePresence( 'genuine_seedling', 'create' )
			->notEmptyString( 'genuine_seedling' );

		$validator
			->numeric( 'offset' )
			->allowEmptyString( 'offset' );

		$validator
			->allowEmptyString( 'row' );

        $validator
            ->allowEmptyString( 'dont_eliminate' );

		$validator
			->allowEmptyString( 'note' );

		$validator
			->allowEmptyString( 'grafting' );

		$validator
			->allowEmptyString( 'rootstock' );

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
		$rules->add( $rules->existsIn( [ 'variety_id' ], 'Varieties' ) );

		return $rules;
	}
}
