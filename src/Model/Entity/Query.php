<?php

namespace App\Model\Entity;

use App\Utility\RulesetToConditionsConverter;
use Cake\ORM\Entity;

/**
 * Query Entity
 *
 * @property int $id
 * @property string $code
 * @property string $my_query
 * @property string $description
 * @property int $query_group_id
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\QueryGroup $query_group
 */
class Query extends Entity {
	
	/**
	 * Fields that can be mass assigned using newEntity() or patchEntity().
	 *
	 * Note that when '*' is set to true, this allows all unspecified fields to
	 * be mass assigned. For security purposes, it is advised to set '*' to false
	 * (or remove it), and explicitly make individual fields accessible as needed.
	 *
	 * @var array
	 */
	protected $_accessible = [
		'*'  => true,
		'id' => false
	];
	
	/**
	 * @var \stdClass with the unserialized query
	 */
	private $q;
	
	/**
	 * @var array with the regular fields using table.field notation
	 */
	private $r_fields;
	
	/**
	 * @var array with the mark fields
	 */
	private $m_fields;
	
	/**
	 * @var array|null with the regular filter conditions
	 */
	private $r_conditions;
	
	/**
	 * @var array with the mark value filter conditions
	 */
	private $m_conditions;
	
	/**
	 * Return the breeding object aggregation mode
	 *
	 * @return string
	 */
	protected function _getMode(): string {
		return $this->_getQuery()->breeding_obj_aggregation_mode;
	}
	
	/**
	 * Return an object with the query data
	 *
	 * @return \stdClass
	 */
	protected function _getQuery(): \stdClass {
		$my_query = $this->_properties['my_query'];
		if ( empty( $q ) || $this->dirty( 'my_query' ) ) {
			$this->q = json_decode( $my_query );
		}
		
		return $this->q;
	}
	
	/**
	 * Return array with active fields in dot notation. Mark properties are excluded.
	 *
	 * @return array
	 */
	protected function _getRegularFields(): array {
		// if self::r_fields is empty or if self::query has changed
		if ( empty( $this->r_fields ) || $this->dirty( 'my_query' ) ) {
			// extract active fields from self::q
			$q              = $this->_getQuery();
			$this->r_fields = [];
			foreach ( $q->fields as $view => $fields ) {
				if ( 'MarkProperties' === $view ) {
					// skip them, cause they are totally different and don't belong to a view
					continue;
				}
				
				foreach ( $fields as $field => $active ) {
					if ( $active ) {
						$this->r_fields[] = $view . '.' . $field;
					}
				}
			};
		}
		
		return $this->r_fields;
	}
	
	/**
	 * Return object with filter conditions or null if no filter conditions were set.
	 *
	 * @return null|array
	 */
	protected function _getRegularConditions() {
		// if self::r_conditions is empty or if self::query has changed
		if ( empty( $this->r_conditions ) || $this->dirty( 'my_query' ) ) {
			$q = $this->_getQuery();
			if ( property_exists( $q, 'where' ) ) {
				$where_rules        = json_decode( $q->where );
				$converter          = new RulesetToConditionsConverter();
				$conditions         = $converter->convertRuleset( $where_rules );
				$this->r_conditions = $conditions;
			} else {
				$this->r_conditions = null;
			}
		}
		
		return $this->r_conditions;
	}
	
	/**
	 * Return array with the mark value filter conditions.
	 *
	 * @return array
	 */
	protected function _getMarkConditions(): array {
		// if self::m_conditions is empty or if self::query has changed
		if ( empty( $this->m_conditions ) || $this->dirty( 'my_query' ) ) {
			$q = $this->_getQuery();
			
			$this->m_conditions = [];
			foreach ( $q->fields->MarkProperties as $id => $obj ) {
				if ( $obj->check ) {
					$this->m_conditions[ $id ] = new MarkFilter(
						$obj->mode,
						$obj->operator,
						$obj->value
					);
				}
			}
		}
		
		return $this->m_conditions;
	}
	
	/**
	 * Return array with the active mark properties
	 *
	 * @return array
	 */
	protected function _getMarkFieldIds(): array {
		// if self::r_fields is empty or if self::query has changed
		if ( empty( $this->m_fields ) || $this->dirty( 'my_query' ) ) {
			$q = $this->_getQuery();
			
			$this->m_fields = [];
			foreach ( $q->fields->MarkProperties as $id => $obj ) {
				if ( $obj->check ) {
					$this->m_fields[] = $id;
				}
			}
		}
		
		return $this->m_fields;
	}
	
	/**
	 * Return array with all possible breeding object aggregation modes.
	 *
	 * @return array
	 */
	protected function _getBreedingObjectAggregationModes(): array {
		return [
			'trees'     => __( 'Trees' ),
			'varieties' => __( 'Varieties' ),
			'batches'   => __( 'Batches' ),
			'convar'    => __( 'Convar' ),
		];
	}
	
}
