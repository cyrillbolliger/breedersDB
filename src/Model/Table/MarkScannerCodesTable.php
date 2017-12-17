<?php

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use ArrayObject;
use Cake\Event\Event;


/**
 * MarkScannerCodes Model
 *
 * @property \Cake\ORM\Association\BelongsTo $MarkFormProperties
 *
 * @method \App\Model\Entity\MarkScannerCode get( $primaryKey, $options = [] )
 * @method \App\Model\Entity\MarkScannerCode newEntity( $data = null, array $options = [] )
 * @method \App\Model\Entity\MarkScannerCode[] newEntities( array $data, array $options = [] )
 * @method \App\Model\Entity\MarkScannerCode|bool save( \Cake\Datasource\EntityInterface $entity, $options = [] )
 * @method \App\Model\Entity\MarkScannerCode patchEntity( \Cake\Datasource\EntityInterface $entity, array $data, array $options = [] )
 * @method \App\Model\Entity\MarkScannerCode[] patchEntities( $entities, array $data, array $options = [] )
 * @method \App\Model\Entity\MarkScannerCode findOrCreate( $search, callable $callback = null, $options = [] )
 */
class MarkScannerCodesTable extends Table {
	
	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 *
	 * @return void
	 */
	public function initialize( array $config ) {
		parent::initialize( $config );
		
		$this->table( 'mark_scanner_codes' );
		$this->displayField( 'id' );
		$this->primaryKey( 'id' );
		
		$this->belongsTo( 'MarkFormProperties', [
			'foreignKey' => 'mark_form_property_id',
			'joinType'   => 'INNER'
		] );
		
		$this->addBehavior( 'Printable' );
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
			->allowEmpty( 'id', 'create' );
		
		$validator
			->requirePresence( 'code', 'create' )
			->notEmpty( 'code' );
		
		$validator
			->requirePresence( 'mark_value', 'create' )
			->notEmpty( 'mark_value' );
		
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
		$rules->add( $rules->existsIn( [ 'mark_form_property_id' ], 'MarkFormProperties' ) );
		$rules->add( $rules->isUnique( [ 'id' ] ) );
		$rules->add( $rules->isUnique( [ 'code' ], __( 'This code already exists.' ) ) );
		$rules->add( $rules->isUnique(
			[ 'mark_form_property_id', 'mark_value' ],
			__( 'A code with this property and this value does already exist.' )
		) );
		
		return $rules;
	}
	
	/**
	 * Return next free scanner code
	 */
	public function getNextFreeCode() {
		$query = $this->find()
		              ->order( [ 'code' => 'DESC' ] )
		              ->first();
		
		if ( empty( $query->code ) ) {
			$code = 1;
		} else {
			$code = ( (int) preg_replace( '/\D/', '', $query->code ) ) + 1;
		}
		
		return sprintf( 'M%05d', $code );
	}
	
	/**
	 * Return query filtered by given search term searching the mark form property type
	 *
	 * @param string $term
	 *
	 * @return \Cake\ORM\Query
	 */
	public function filter( string $term ) {
		$query = $this->find()
		              ->contain( [ 'MarkFormProperties' ] );
		if ( $term ) {
			$query->where( [ 'mark_form_property_id' => (int) $term ] );
		}
		
		return $query;
	}
	
	/**
	 * Return label to print in Zebra Printing Language
	 *
	 * @param int $id
	 *
	 * @return string
	 */
	public function getLabelZpl( int $id ) {
		$entity      = $this->get( $id, [ 'contain' => [ 'MarkFormProperties' ] ] );
		$description = $entity->mark_form_property->name . ": " . $entity->mark_value;
		$code        = $entity->code;
		
		return $this->getZPL( $description, $code );
	}
	
	/**
	 * Return label to print in Zebra Printing Language
	 */
	public function getSubmitLabelZpl() {
		$code        = "SUBMIT";
		$description = "SUBMIT";
		
		return $this->getZPL( $description, $code );
	}
	
	
}
