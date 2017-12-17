<?php

namespace App\Model\Rule;

use Cake\Datasource\EntityInterface;
use Cake\ORM\TableRegistry;

/**
 * Return false if there is associated data to this record
 *
 * Use an array ['Table'=>'foreign_key'] to check the associations.
 */
class IsNotReferredBy {
	protected $associations;
	
	public function __construct( array $associations ) {
		$this->associations = $associations;
	}
	
	public function __invoke( EntityInterface $entity, array $options ) {
		foreach ( $this->associations as $table_name => $field ) {
			$table = TableRegistry::get( $table_name );
			
			$associated = $table->find()->where( [ $field => $entity->id ] )->count();
			
			if ( $associated ) {
				return false;
			}
		}
		
		return true;
	}
}