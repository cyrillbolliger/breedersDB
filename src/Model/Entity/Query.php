<?php

namespace App\Model\Entity;

use App\Utility\RulesetToConditionsConverter;
use Cake\ORM\Entity;
use Cake\ORM\Locator\TableLocator;

/**
 * Query Entity
 *
 * @property int $id
 * @property string $code
 * @property string $my_query
 * @property string $description
 * @property int $query_group_id
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property ?\Cake\I18n\FrozenTime $deleted
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
	 * @var array with the active regular fields using table.field notation
	 */
	private $r_active_fields;

	/**
	 * @var array with the active mark fields
	 */
	private $m_active_field_ids;

	/**
	 * @var array with all mark fields
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
	 * @var array with the active regular table names
	 */
	private $r_active_tables;

    public function __construct(array $properties = [], array $options = [])
    {
        parent::__construct($properties, $options);

        if (!$this->isVersionNull()) {
            $this->setVirtual(['raw_query']);
        }
    }

    /**
	 * Return array with active fields in dot notation. Mark properties are excluded.
	 *
	 * @return array
	 */
	protected function _getActiveRegularFields(): array {
        if (! $this->isVersionNull()) {
            throw new \RuntimeException("Query version 'null' method called on query of higher version.");
        }

		// if self::r_fields is empty or if self::query has changed
		if ( empty( $this->r_active_fields ) || $this->isDirty( 'my_query' ) ) {
			// extract active fields from self::q
			$q                     = $this->_getQuery();
			$this->r_active_fields = [];
			foreach ( $q->fields as $view => $fields ) {
				if ( 'MarkProperties' === $view ) {
					// skip them, cause they are totally different and don't belong to a view
					continue;
				}

				foreach ( $fields as $field => $active ) {
					if ( $active ) {
						$this->r_active_fields[] = $view . '.' . $field;
					}
				}
			};
		}

		return $this->r_active_fields;
	}

	/**
	 * Return an object with the query data
	 *
	 * @return \stdClass
	 */
	protected function _getQuery(): \stdClass {
        if (! $this->isVersionNull()) {
            throw new \RuntimeException("Query version 'null' method called on query of higher version.");
        }

		if ( ! isset( $this->_fields['my_query'] ) ) {
			return (object) [];
		}

		$my_query = $this->_fields['my_query'];
		if ( empty( $this->q ) || $this->isDirty( 'my_query' ) ) {
			$this->q = json_decode( $my_query );
		}

		return $this->q;
	}

    protected function _getRawQuery(): \stdClass {
        return json_decode($this->my_query, false, 512, JSON_THROW_ON_ERROR);
    }

	/**
	 * Return object with filter conditions or null if no filter conditions were set.
	 *
	 * @return null|array
	 */
	protected function _getRegularConditions() {
        if (! $this->isVersionNull()) {
            throw new \RuntimeException("Query version 'null' method called on query of higher version.");
        }

		// if self::r_conditions is empty or if self::query has changed
		if ( empty( $this->r_conditions ) || $this->isDirty( 'my_query' ) ) {
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
        if (! $this->isVersionNull()) {
            throw new \RuntimeException("Query version 'null' method called on query of higher version.");
        }

		// if self::m_conditions is empty or if self::query has changed
		if ( empty( $this->m_conditions ) || $this->isDirty( 'my_query' ) ) {
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
	protected function _getActiveMarkFieldIds(): array {
        if (! $this->isVersionNull()) {
            throw new \RuntimeException("Query version 'null' method called on query of higher version.");
        }

		// if self::r_fields is empty or if self::query has changed
		if ( empty( $this->m_active_field_ids ) || $this->isDirty( 'my_query' ) ) {
			$q = $this->_getQuery();

			$this->m_active_field_ids = [];
			foreach ( $q->fields->MarkProperties as $id => $obj ) {
				if ( $obj->check ) {
					$this->m_active_field_ids[] = $id;
				}
			}
		}

		return $this->m_active_field_ids;
	}

	/**
	 * Return array with all possible breeding object aggregation modes.
	 *
	 * @return array
	 */
	protected function _getBreedingObjectAggregationModes(): array {
        if (! $this->isVersionNull()) {
            throw new \RuntimeException("Query version 'null' method called on query of higher version.");
        }

		return [
			'trees'     => __( 'Trees' ),
			'varieties' => __( 'Varieties' ),
			'batches'   => __( 'Batches' ),
			'convar'    => __( 'Convar' ),
		];
	}

	/**
	 * Return the breeding object aggregation mode. defaults to 'convar' if not set.
	 *
	 * @return string
	 */
	protected function _getBreedingObjectAggregationMode(): string {
        if (! $this->isVersionNull()) {
            throw new \RuntimeException("Query version 'null' method called on query of higher version.");
        }

		if ( ! isset( $this->_getQuery()->breeding_obj_aggregation_mode ) ) {
			return 'convar';
		}

		return $this->_getQuery()->breeding_obj_aggregation_mode;
	}

	/**
	 * Return names of active tables
	 *
	 * @return array
	 */
	protected function _getActiveViewTables(): array {
        if (! $this->isVersionNull()) {
            throw new \RuntimeException("Query version 'null' method called on query of higher version.");
        }

		// if self::r_active_tables is empty or if self::query has changed
		if ( empty( $this->r_active_tables ) || $this->isDirty( 'my_query' ) ) {
			// extract active tables from self::q
			$q                     = $this->_getQuery();
			$this->r_active_tables = [];
			foreach ( $q->fields as $view => $fields ) {
				if ( 'MarkProperties' === $view ) {
					// skip it, cause its no real table
					continue;
				}

				foreach ( $fields as $field => $active ) {
					if ( $active && ! in_array( $view, $this->r_active_tables ) ) {
						$this->r_active_tables[] = $view;
					}
				}
			};
		}

		return $this->r_active_tables;
	}

	/**
	 * Return a stdClass containing the where rules
	 *
	 * @return \stdClass
	 */
	protected function _getWhereRules(): \stdClass {
        if (! $this->isVersionNull()) {
            throw new \RuntimeException("Query version 'null' method called on query of higher version.");
        }

		return json_decode( $this->_getWhereRulesJson() );
	}

	/**
	 * Return the where data as JSON or null
	 *
	 * @return string JSON of the where data
	 */
	protected function _getWhereRulesJson(): string {
        if (! $this->isVersionNull()) {
            throw new \RuntimeException("Query version 'null' method called on query of higher version.");
        }

		if ( ! isset( $this->_getQuery()->where ) ) {
			return json_encode( null );
		}

		return $this->_getQuery()->where;
	}

	/**
	 * Return array with the mark property ids as keys and the mark properties as values.
	 *
	 * Example:
	 * [
	 *  12 => object(stdClass) {
	 *      check => '0'
	 *      mode => 'count'
	 *      operator => 'median'
	 *      value => 'greater'
	 *  }
	 * ]
	 *
	 * @return array
	 */
	protected function _getMarkFields(): array {
        if (! $this->isVersionNull()) {
            throw new \RuntimeException("Query version 'null' method called on query of higher version.");
        }

		// if self::m_fields is empty or if self::query has changed
		if ( empty( $this->m_fields ) || $this->isDirty( 'my_query' ) ) {
			// extract fields from self::q
			$q              = $this->_getQuery();
			$fields         = $q->fields->MarkProperties;
			$this->m_fields = [];
			foreach ( $fields as $key => $val ) {
				// we need to do this, since we want an array with numeric keys,
				// but receive an object. Direct casting would give us string keys.
				$this->m_fields[ (int) $key ] = $val;
			}
		}

		return $this->m_fields;
	}

    private function isVersionNull(): bool
    {
        return null === $this->getVersion();
    }

    private function getVersion(): null|string
    {
        if (empty($this->query_group_id)) {
            return null;
        }

        return \Cake\Datasource\FactoryLocator::get('Table')
            ->get('QueryGroups')
            ->get($this->query_group_id)
            ->version;
    }
}
