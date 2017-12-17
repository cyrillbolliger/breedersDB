<?php

namespace App\Model\Table;

use App\Model\Behavior\GetFilterDataBehavior;
use App\Model\Behavior\TranslatableFieldsBehavior;
use App\Model\Entity\Query;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Association\HasMany;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;
use SoftDelete\Model\Table\SoftDeleteTrait;

/**
 * Queries Model
 *
 * @property \Cake\ORM\Association\BelongsTo $QueryGroups
 *
 * @method \App\Model\Entity\Query get( $primaryKey, $options = [] )
 * @method \App\Model\Entity\Query newEntity( $data = null, array $options = [] )
 * @method \App\Model\Entity\Query[] newEntities( array $data, array $options = [] )
 * @method \App\Model\Entity\Query|bool save( \Cake\Datasource\EntityInterface $entity, $options = [] )
 * @method \App\Model\Entity\Query patchEntity( \Cake\Datasource\EntityInterface $entity, array $data, array $options = [] )
 * @method \App\Model\Entity\Query[] patchEntities( $entities, array $data, array $options = [] )
 * @method \App\Model\Entity\Query findOrCreate( $search, callable $callback = null, $options = [] )
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @mixin GetFilterDataBehavior
 * @mixin TranslatableFieldsBehavior
 */
class QueriesTable extends Table {
	use SoftDeleteTrait;
	
	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 *
	 * @return void
	 */
	public function initialize( array $config ) {
		parent::initialize( $config );
		
		$this->table( 'queries' );
		$this->displayField( 'id' );
		$this->primaryKey( 'id' );
		
		$this->addBehavior( 'Timestamp' );
		$this->addBehavior( 'GetFilterData' );
		$this->addBehavior( 'TranslatableFields' );
		
		$this->belongsTo( 'QueryGroups', [
			'foreignKey' => 'query_group_id',
			'joinType'   => 'INNER'
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
			->integer( 'id' )
			->allowEmpty( 'id', 'create' )
			->add( 'id', 'unique', [ 'rule' => 'validateUnique', 'provider' => 'table' ] );
		
		$validator
			->requirePresence( 'code', 'create' )
			->notEmpty( 'code' )
			->add( 'code', 'unique', [ 'rule' => 'validateUnique', 'provider' => 'table' ] );
		
		$validator
			->allowEmpty( 'query' );
		
		$validator
			->allowEmpty( 'description' );
		
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
		$rules->add( $rules->isUnique( [ 'id' ] ) );
		$rules->add( $rules->isUnique( [ 'code' ] ) );
		$rules->add( $rules->existsIn( [ 'query_group_id' ], 'QueryGroups' ) );
		
		return $rules;
	}
	
	/**
	 * Return associative array with view.field as key and its translated name as value
	 * grouped by the view name.
	 *
	 * @param array $tables
	 *
	 * @return array
	 *
	 * @throws \Exception if field key doesn't exist in translations array
	 */
	public function getTranslatedFieldsOf( array $tables ) {
		$view_fields_type_map = $this->getFieldTypeMapOf( $tables );
		
		$fields = array();
		foreach ( $view_fields_type_map as $view => $fields_type_map ) {
			foreach ( $fields_type_map as $field => $type ) {
				$fields[ $view ][ $view . '.' . $field ] = $this->translateFields( $view . '.' . $field );
			}
		}
		
		return $fields;
	}
	
	/**
	 * Return associative array with field names as keys and its type as value from given tables names
	 *
	 * @param array $tables
	 *
	 * @return array
	 */
	public function getFieldTypeMapOf( array $tables ) {
		$fields = array();
		foreach ( $tables as $table_name ) {
			$table                 = TableRegistry::get( $table_name );
			$fields[ $table_name ] = $table->schema()->typeMap();
		}
		
		return $fields;
	}
	
	/**
	 * Return patched entity with the query data merged as json into the query field.
	 * The other fields are patched normally.
	 *
	 * @param $entity
	 * @param $request
	 *
	 * @return mixed
	 */
	public function patchEntityWithQueryData( $entity, $request ) {
		// add root view
		$data['root_view'] = $request['root_view'];
		unset( $request['root_view'] );
		
		// add breeding object aggregation mode
		if ( 'MarksView' === $data['root_view'] ) {
			$data['breeding_obj_aggregation_mode'] = $request['breeding_object_aggregation_mode'];
			unset( $request['breeding_object_aggregation_mode'] );
		}
		
		// add fields
		$views = array_keys( $this->getViewNames() );
		$query = array();
		foreach ( $views as $view ) {
			$query[ $view ] = $request[ $view ];
			unset( $request[ $view ] );
		}
		
		// manually add mark property fields, because there is no corresponding view
		$query['MarkProperties'] = $request['MarkProperties'];
		unset( $request['MarkProperties'] );
		
		$data['fields'] = $query;
		
		// add regular filter
		$data['where'] = $request['where_query'];
		unset( $request['where_query'] );
		
		// pack it all in the database' query field
		$request['my_query'] = json_encode( $data );
		
		return $this->patchEntity( $entity, $request );
	}
	
	/**
	 * Return associative array with names of the views to query as keys and its translated names as values
	 *
	 * @return array
	 */
	public function getViewNames() {
		return [
			'BatchesView'       => __( 'Batches' ),
			'CrossingsView'     => __( 'Crossings' ),
			'MarksView'         => __( 'Marks' ),
			'MotherTreesView'   => __( 'Mother Trees' ),
			'ScionsBundlesView' => __( 'Scions Bundles' ),
			'TreesView'         => __( 'Trees' ),
			'VarietiesView'     => __( 'Varieties' ),
		];
	}
	
	/**
	 * Return columns from given query with dot noted key and translated value
	 *
	 * @param $query
	 *
	 * @return array
	 *
	 * @throws \Exception if field key doesn't exist in translations array
	 */
	public function getViewQueryColumns( Query $query ) {
		$tmp = $this->_getTranslatedFieldsFromList( $query->active_regular_fields );
		
		// get recursively dot notated keys
		$return = [];
		foreach ( $tmp as $key => $value ) {
			$dot_key            = $this->_getDottedFieldPath( $key );
			$return[ $dot_key ] = $value;
		}
		
		return $return;
	}
	
	/**
	 * Return associative array with view.field as key and its translated name as value.
	 *
	 * @param array $list
	 *
	 * @return array
	 *
	 * @throws \Exception if field key doesn't exist in translations array
	 */
	private function _getTranslatedFieldsFromList( array $list ) {
		$fields = [];
		foreach ( $list as $item ) {
			$fields[ $item ] = $this->translateFields( $item );
		}
		
		return $fields;
	}
	
	/**
	 * Return fully qualified dot path of given key.
	 * IMPORTANT: make sure this method gets called AFTER $this->buildViewQuery()
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	private function _getDottedFieldPath( string $key ): string {
		$table = explode( '.', $key )[0];
		foreach ( $this->viewQueryAssociations as $association ) {
			$path = $this->viewQueryRoot . '.' . $association;
			$pos  = strpos( $path, $table );
			if ( $pos ) {
				return substr( $path, 0, $pos ) . $key;
			}
		}
		
		return $key;
	}
	
	/**
	 * Return query from given query data.
	 *
	 * The 'where' property is optional.
	 * If present the unwrapped JSON from the where query builder is expected.
	 * (@see http://querybuilder.js.org/)
	 *
	 * @param \App\Model\Entity\Query $query
	 *
	 * @return \Cake\ORM\Query
	 *
	 * @throws \Exception if association type of selected tables is not implemented
	 */
	public function buildViewQuery( Query $query ) {
		$tables = $query->active_view_tables;
		
		// keep in memory for later use
		$this->viewQueryRoot         = $query->query->root_view;
		$associations                = $this->_buildAssociationsForContainStatement( $this->viewQueryRoot, $tables );
		$this->viewQueryAssociations = $associations;
		
		$joins      = $this->_buildJoinArrays( $associations );
		$conditions = $query->regular_conditions;
		
		$rootTable = TableRegistry::get( $this->viewQueryRoot );
		$orm_query = $rootTable
			->find( 'all' )
			->contain( $associations )
			->join( $joins )
			->where( $conditions );
		
		return $orm_query;
	}
	
	/**
	 * Return 1-dim array with association paths of $tables in dot notation starting from root.
	 *
	 * @param string $root
	 * @param array $tables
	 *
	 * @return array
	 */
	private function _buildAssociationsForContainStatement( string $root, array $tables ) {
		$associations = $this->_getAssociationsRecursive( $root, $tables );
		
		if ( empty( $associations ) ) {
			return [];
		}
		
		return $this->_getDottedArrayPath( $associations );
	}
	
	/**
	 * Return nested array of associations of all $tables starting at the root.
	 * The key represent the association. Leafs have null values.
	 *
	 * @param string $root
	 * @param array $tables
	 *
	 * @return array|null
	 */
	private function _getAssociationsRecursive( string $root, array $tables ) {
		$tables = array_diff( $tables, [ $root ] );
		
		if ( empty( $tables ) ) {
			return null;
		}
		
		$associations = array();
		$return       = array();
		
		foreach ( $this->getAssociationsOf( $root ) as $association ) {
			$key = array_search( $association, $tables );
			if ( false !== $key ) {
				$associations[] = $tables[ $key ];
				unset( $tables[ $key ] );
			}
		}
		
		if ( empty( $associations ) ) {
			return null;
		}
		
		foreach ( $associations as $key => $association ) {
			$return[ $association ] = $this->_getAssociationsRecursive( $association, $tables );
		}
		
		return $return;
	}
	
	/**
	 * Return array of associations from given table
	 *
	 * @param string $table_name
	 * @param boolean $hasManyOnly only return has many associations
	 *
	 * @return array of associations
	 */
	public function getAssociationsOf( string $table_name, $hasManyOnly = false ) {
		$associated = array();
		
		$tmp = TableRegistry::get( $table_name );
		$has = $tmp->associations();
		
		$tables = array();
		foreach ( $has as $table => $properties ) {
			$tables[] = $table;
		}
		
		if ( $hasManyOnly ) {
			foreach ( $tables as $table ) {
				if ( ! $has->get( $table ) instanceof \Cake\ORM\Association\HasMany ) {
					unset( $tables[ array_search( $table, $tables ) ] );
				}
			}
		}
		
		foreach ( $tables as $table ) {
			$associated[] = $has->get( $table )->name();
		}
		
		return $associated;
	}
	
	/**
	 * Recursive walk array and build a dot path of its keys.
	 *
	 * @param $array
	 *
	 * @return array
	 */
	private function _getDottedArrayPath( $array ): array {
		$return = [];
		foreach ( $array as $base => $child ) {
			if ( ! is_array( $child ) ) {
				$return[] = $base;
			} else {
				$return[] = trim( $base . '.' . implode( '.', $this->_getDottedArrayPath( $child ) ), '.' );
			}
		}
		
		return $return;
	}
	
	/**
	 * Return an array ready for the cake ORMs join method from given array with a dot noted list of associations
	 *
	 * @param array $associations
	 *
	 * @return array
	 *
	 * @throws \Exception if association type is not implemented
	 */
	private function _buildJoinArrays( array $associations ) {
		
		$array = array();
		foreach ( $associations as $els ) {
			$rootTable = TableRegistry::get( $this->viewQueryRoot );
			foreach ( explode( '.', $els ) as $tableName ) {
				$table               = TableRegistry::get( $tableName );
				$rootAssociation     = $rootTable->associations()->get( $table->alias() );
				$tableAssociation    = $table->associations()->get( $rootTable->alias() );
				$array[ $tableName ] = [
					'table'      => $table->table(),
					'conditions' => $this->_getAssociationConditions( $rootAssociation, $tableAssociation ),
					'type'       => $rootAssociation->joinType()
				];
				$rootTable           = $table;
			}
		}
		
		return $array;
	}
	
	/**
	 * Return the SQL join condition from given table associations to join
	 *
	 * @param $associationA
	 * @param $associationB
	 *
	 * @return string
	 * @throws \Exception if association type is not implemented
	 */
	private function _getAssociationConditions( $associationA, $associationB ) {
		if ( $associationA instanceof BelongsTo && $associationB instanceof HasMany ) {
			$leaf = $associationA;
			$root = $associationB;
		}
		if ( $associationA instanceof HasMany && $associationB instanceof BelongsTo ) {
			$leaf = $associationB;
			$root = $associationA;
		}
		
		if ( empty( $leaf ) || empty( $root ) ) {
			throw new \Exception( "Type of association not implemented" );
		}
		
		return $root->name() . '.' . $root->foreignKey() . ' = ' . $leaf->name() . '.' . $leaf->bindingKey();
	}
	
	/**
	 * Return array with the field key as key and the translated field name as name
	 *
	 * @param Query $markQuery
	 *
	 * @return array
	 *
	 * @throws \Exception if field key doesn't exist in translations array
	 */
	public function getRegularColumns( Query $markQuery ): array {
		$columns = [];
		foreach ( $markQuery->active_regular_fields as $field ) {
			$key             = explode( '.', $field )[1];
			$columns[ $key ] = $this->translateFields( $field );
		}
		
		return $columns;
	}
	
	/**
	 * Return array with the field key as key and the MarkFormProperty as value
	 *
	 * @param Query $markQuery
	 *
	 * @return array
	 *
	 * @throws \Exception if field key doesn't exist in translations array
	 */
	public function getMarkColumns( Query $markQuery ): array {
		$markProperties = TableRegistry::get( 'MarkFormProperties' );
		$columns        = [];
		foreach ( $markQuery->active_mark_field_ids as $property_id ) {
			$columns[ $property_id ] = $markProperties->get( $property_id );
		}
		
		return $columns;
	}
}
