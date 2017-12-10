<?php

namespace App\Model\Table;

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
	
	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 *
	 * @return void
	 */
	public function initialize( array $config ) {
		parent::initialize( $config );
		
		$this->table( 'marks_view' );
		$this->displayField( 'name' );
		$this->primaryKey( 'id' );
		
		$this->addBehavior( 'MarkQuery' );
		
		$this->belongsTo( 'TreesView', [
			'foreignKey' => 'tree_id'
		] );
		$this->belongsTo( 'VarietiesView', [
			'foreignKey' => 'variety_id'
		] );
		$this->belongsTo( 'BatchesView', [
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
	public function validationDefault( Validator $validator ) {
		$validator
			->date( 'date' )
			->allowEmpty( 'date' );
		
		$validator
			->allowEmpty( 'author' );
		
		$validator
			->requirePresence( 'value', 'create' )
			->notEmpty( 'value' );
		
		$validator
			->boolean( 'exceptional_mark' )
			->requirePresence( 'exceptional_mark', 'create' )
			->notEmpty( 'exceptional_mark' );
		
		$validator
			->requirePresence( 'name', 'create' )
			->notEmpty( 'name' );
		
		$validator
			->requirePresence( 'field_type', 'create' )
			->notEmpty( 'field_type' );
		
		$validator
			->requirePresence( 'mark_form_property_type', 'create' )
			->notEmpty( 'mark_form_property_type' );
		
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
		$rules->add( $rules->existsIn( [ 'tree_id' ], 'Trees' ) );
		$rules->add( $rules->existsIn( [ 'variety_id' ], 'Varieties' ) );
		$rules->add( $rules->existsIn( [ 'batch_id' ], 'Batches' ) );
		
		return $rules;
	}
}
