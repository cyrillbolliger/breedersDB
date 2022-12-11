<?php

namespace App\Model\Table;

use App\Model\Behavior\FilterSchemaBehavior;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ScionsBundlesView Model
 *
 * @property \Cake\ORM\Association\BelongsTo $VarietiesView
 *
 * @method \App\Model\Entity\ScionsBundlesView get( $primaryKey, $options = [] )
 * @method \App\Model\Entity\ScionsBundlesView newEntity( $data = null, array $options = [] )
 * @method \App\Model\Entity\ScionsBundlesView[] newEntities( array $data, array $options = [] )
 * @method \App\Model\Entity\ScionsBundlesView|bool save( \Cake\Datasource\EntityInterface $entity, $options = [] )
 * @method \App\Model\Entity\ScionsBundlesView patchEntity( \Cake\Datasource\EntityInterface $entity, array $data, array $options = [] )
 * @method \App\Model\Entity\ScionsBundlesView[] patchEntities( $entities, array $data, array $options = [] )
 * @method \App\Model\Entity\ScionsBundlesView findOrCreate( $search, callable $callback = null, $options = [] )
 *
 * @mixin FilterSchemaBehavior
 */
class ScionsBundlesViewTable extends Table {
	/**
	 * boolean fields
	 */
	private $boolean = [ 'external_use' ];

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

    public function getTranslatedName(): string {
        return __('Scions Bundles');
    }

    public function getTranslatedColumnName(string $column): ?string {
        $columns = [
            'id'                     => __( 'Id' ),
            'identification'         => __( 'Identification' ),
            'convar'                 => __( 'Convar' ),
            'numb_scions'            => __( 'Numb Scions' ),
            'date_scions_harvest'    => __( 'Date Scions Harvest' ),
            'descents_publicid_list' => __( 'Descents (Publicids)' ),
            'note'                   => __( 'Note' ),
            'external_use'           => __( 'Reserved for external partners' ),
            'variety_id'             => __( 'Varienty Id' ),
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

		$this->setTable( 'scions_bundles_view' );
		$this->setDisplayField( 'code' );
		$this->setPrimaryKey( 'id' );

        $this->addBehavior( 'FilterSchema' );

		$this->belongsTo( 'VarietiesView', [
			'foreignKey' => 'variety_id',
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
			->requirePresence( 'identification', 'create' )
			->notEmptyString( 'identification' );

		$validator
			->requirePresence( 'convar', 'create' )
			->notEmptyString( 'convar' );

		$validator
			->integer( 'numb_scions' )
			->allowEmptyString( 'numb_scions' );

		$validator
			->date( 'date_scions_harvest' )
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
	public function buildRules( RulesChecker $rules ): \Cake\ORM\RulesChecker {
		$rules->add( $rules->existsIn( [ 'variety_id' ], 'Varieties' ) );

		return $rules;
	}
}
