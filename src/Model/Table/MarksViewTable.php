<?php

namespace App\Model\Table;

use App\Model\Behavior\FilterSchemaBehavior;
use App\Model\Behavior\MarkQueryBehavior;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MarksView Model
 *
 * @property \Cake\ORM\Association\BelongsTo $TreesView
 * @property \Cake\ORM\Association\BelongsTo $VarietiesView
 * @property \Cake\ORM\Association\BelongsTo $BatchesView
 *
 * @method \App\Model\Entity\MarksView get( $primaryKey, $options = [] )
 * @method \App\Model\Entity\MarksView newEntity( $data = null, array $options = [] )
 * @method \App\Model\Entity\MarksView[] newEntities( array $data, array $options = [] )
 * @method \App\Model\Entity\MarksView|bool save( \Cake\Datasource\EntityInterface $entity, $options = [] )
 * @method \App\Model\Entity\MarksView patchEntity( \Cake\Datasource\EntityInterface $entity, array $data, array $options = [] )
 * @method \App\Model\Entity\MarksView[] patchEntities( $entities, array $data, array $options = [] )
 * @method \App\Model\Entity\MarksView findOrCreate( $search, callable $callback = null, $options = [] )
 *
 * @mixin MarkQueryBehavior
 * @mixin FilterSchemaBehavior
 */
class MarksViewTable extends Table {
	/**
	 * boolean fields
	 */
	private $boolean = [ 'exceptional_mark' ];

	/**
	 * select fields
	 */
	private $select = [ 'name', 'field_type', 'mark_form_property_type' ];

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

    public function getTranslatedName(): string
    {
        return __('Marks');
    }

    public function getTranslatedColumnName(string $column): ?string
    {
        $columns = [
            'id' => __('Id'),
            'mark_id' => __('Mark Id'),
            'date' => __('Date'),
            'author' => __('Author'),
            'tree_id' => __('Tree Id'),
            'variety_id' => __('Variety Id'),
            'batch_id' => __('Batch Id'),
            'value' => __('Value'),
            'exceptional_mark' => __('Exceptional Mark'),
            'name' => __('Property'),
            'property_id' => __('Property Id'),
            'field_type' => __('Data Type'),
            'property_type' => __('Property Type'),
            'created' => __('Created'),
            'mark_form_id' => __('Form Id'),
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

		$this->setTable( 'marks_view' );
		$this->setDisplayField( 'name' );
		$this->setPrimaryKey( 'id' );

		$this->addBehavior( 'MarkQuery' );
        $this->addBehavior( 'FilterSchema' );

		$this->belongsTo( 'TreesView', [
			'foreignKey' => 'tree_id',
		] );
		$this->belongsTo( 'VarietiesView', [
			'foreignKey' => 'variety_id',
		] );
		$this->belongsTo( 'BatchesView', [
			'foreignKey' => 'batch_id',
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
			->date( 'date' )
			->allowEmptyDate( 'date' );

		$validator
			->allowEmptyString( 'author' );

		$validator
			->requirePresence( 'value', 'create' )
			->notEmptyString( 'value' );

		$validator
			->boolean( 'exceptional_mark' )
			->requirePresence( 'exceptional_mark', 'create' )
			->notEmptyString( 'exceptional_mark' );

		$validator
			->requirePresence( 'name', 'create' )
			->notEmptyString( 'name' );

		$validator
			->requirePresence( 'field_type', 'create' )
			->notEmptyString( 'field_type' );

		$validator
			->requirePresence( 'mark_form_property_type', 'create' )
			->notEmptyString( 'mark_form_property_type' );

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
		$rules->add( $rules->existsIn( [ 'variety_id' ], 'Varieties' ) );
		$rules->add( $rules->existsIn( [ 'batch_id' ], 'Batches' ) );

		return $rules;
	}
}
