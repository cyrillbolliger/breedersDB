<?php

namespace App\Model\Table;

use Cake\Core\Exception\Exception;
use Cake\ORM\Query;
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
 * @method \App\Model\Entity\Query get($primaryKey, $options = [])
 * @method \App\Model\Entity\Query newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Query[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Query|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Query patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Query[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Query findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class QueriesTable extends Table
{
    use SoftDeleteTrait;
    
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     *
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);
        
        $this->table('queries');
        $this->displayField('id');
        $this->primaryKey('id');
        
        $this->addBehavior('Timestamp');
        
        $this->belongsTo('QueryGroups', [
            'foreignKey' => 'query_group_id',
            'joinType'   => 'INNER'
        ]);
    }
    
    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     *
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create')
            ->add('id', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);
        
        $validator
            ->requirePresence('code', 'create')
            ->notEmpty('code')
            ->add('code', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);
        
        $validator
            ->allowEmpty('query');
        
        $validator
            ->allowEmpty('description');
        
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
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->isUnique(['id']));
        $rules->add($rules->isUnique(['code']));
        $rules->add($rules->existsIn(['query_group_id'], 'QueryGroups'));
        
        return $rules;
    }
    
    /**
     * Return associative array with view.field as key and its translated name as value
     * grouped by the view name.
     *
     * @param array $tables
     *
     * @return array
     */
    public function getTranslatedFieldsOf(array $tables)
    {
        $view_fields_type_map = $this->getFieldTypeMapOf($tables);
        
        $fields = array();
        foreach ($view_fields_type_map as $view => $fields_type_map) {
            foreach ($fields_type_map as $field => $type) {
                $fields[$view][$view . '.' . $field] = $this->translateFields($view . '.' . $field);
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
    public function getFieldTypeMapOf(array $tables)
    {
        $fields = array();
        foreach ($tables as $table_name) {
            $table               = TableRegistry::get($table_name);
            $fields[$table_name] = $table->schema()->typeMap();
        }
        
        return $fields;
    }
    
    /**
     * Translate field names
     *
     * @param string $key
     *
     * @return string
     * @throws \Cake\Core\Exception\Exception if given key doesn't exist in translations array
     */
    public function translateFields(string $key)
    {
        $translations = [
            'BatchesView.id'                           => __('Batch -> Id'),
            'BatchesView.crossing_batch'               => __('Batch -> Crossing.Batch'),
            'BatchesView.date_sowed'                   => __('Batch -> Date Sowed'),
            'BatchesView.numb_seeds_sowed'             => __('Batch -> Numb Seeds Sowed'),
            'BatchesView.numb_sprouts_grown'           => __('Batch -> Numb Sprouts Grown'),
            'BatchesView.seed_tray'                    => __('Batch -> Seed Tray'),
            'BatchesView.date_planted'                 => __('Batch -> Date Planted'),
            'BatchesView.numb_sprouts_planted'         => __('Batch -> Numb Sprouts Planted'),
            'BatchesView.patch'                        => __('Batch -> Patch'),
            'BatchesView.note'                         => __('Batch -> Note'),
            'BatchesView.crossing_id'                  => __('Batch -> Crossing Id'),
            'CrossingsView.id'                         => __('Crossing -> Id'),
            'CrossingsView.code'                       => __('Crossing -> Code'),
            'CrossingsView.mother_variety'             => __('Crossing -> Mother Variety'),
            'CrossingsView.father_variety'             => __('Crossing -> Father Variety'),
            'MarksView.id'                             => __('Mark -> Id'),
            'MarksView.date'                           => __('Mark -> Date'),
            'MarksView.author'                         => __('Mark -> Author'),
            'MarksView.tree_id'                        => __('Mark -> Tree Id'),
            'MarksView.variety_id'                     => __('Mark -> Variety Id'),
            'MarksView.batch_id'                       => __('Mark -> Batch Id'),
            'MarksView.value'                          => __('Mark -> Value'),
            'MarksView.exceptional_mark'               => __('Mark -> Exceptional Mark'),
            'MarksView.name'                           => __('Mark -> Property'),
            'MarksView.field_type'                     => __('Mark -> Data Type'),
            'MarksView.mark_form_property_type'        => __('Mark -> Property Type'),
            'MotherTreesView.id'                       => __('Mother Tree -> Id'),
            'MotherTreesView.crossing'                 => __('Mother Tree -> Crossing'),
            'MotherTreesView.code'                     => __('Mother Tree -> Identification'),
            'MotherTreesView.planed'                   => __('Mother Tree -> Planed'),
            'MotherTreesView.date_pollen_harvested'    => __('Mother Tree -> Date Pollen Harvested'),
            'MotherTreesView.date_impregnated'         => __('Mother Tree -> Date Impregnated'),
            'MotherTreesView.date_fruit_harvested'     => __('Mother Tree -> Date Fruit Harvested'),
            'MotherTreesView.numb_portions'            => __('Mother Tree -> Numb Portions'),
            'MotherTreesView.numb_flowers'             => __('Mother Tree -> Numb Flowers'),
            'MotherTreesView.numb_seeds'               => __('Mother Tree -> Numb Seeds'),
            'MotherTreesView.target'                   => __('Mother Tree -> Target'),
            'MotherTreesView.note'                     => __('Mother Tree -> Note'),
            'MotherTreesView.convar'                   => __('Mother Tree -> Convar'),
            'MotherTreesView.publicid'                 => __('Mother Tree -> Publicid'),
            'MotherTreesView.offset'                   => __('Mother Tree -> Offset'),
            'MotherTreesView.row'                      => __('Mother Tree -> Row'),
            'MotherTreesView.experiment_site'          => __('Mother Tree -> Experiment Site'),
            'MotherTreesView.tree_id'                  => __('Mother Tree -> Tree Id'),
            'MotherTreesView.crossing_id'              => __('Mother Tree -> Crossing Id'),
            'ScionsBundlesView.id'                     => __('Scions Bundle -> Id'),
            'ScionsBundlesView.identification'         => __('Scions Bundle -> Identification'),
            'ScionsBundlesView.convar'                 => __('Scions Bundle -> Convar'),
            'ScionsBundlesView.numb_scions'            => __('Scions Bundle -> Numb Scions'),
            'ScionsBundlesView.date_scions_harvest'    => __('Scions Bundle -> Date Scions Harvest'),
            'ScionsBundlesView.descents_publicid_list' => __('Scions Bundle -> Descents (Publicids)'),
            'ScionsBundlesView.note'                   => __('Scions Bundle -> Note'),
            'ScionsBundlesView.external_use'           => __('Scions Bundle -> Reserved for external partners'),
            'ScionsBundlesView.variety_id'             => __('Scions Bundle -> Varienty Id'),
            'TreesView.id'                             => __('Tree -> Id'),
            'TreesView.publicid'                       => __('Tree -> Publicid'),
            'TreesView.convar'                         => __('Tree -> Convar'),
            'TreesView.date_grafted'                   => __('Tree -> Date Grafted'),
            'TreesView.date_planted'                   => __('Tree -> Date Planted'),
            'TreesView.date_eliminated'                => __('Tree -> Date Eliminated'),
            'TreesView.genuine_seedling'               => __('Tree -> Genuine Seedling'),
            'TreesView.offset'                         => __('Tree -> Offset'),
            'TreesView.row'                            => __('Tree -> Row'),
            'TreesView.note'                           => __('Tree -> Note'),
            'TreesView.variety_id'                     => __('Tree -> Variety Id'),
            'TreesView.grafting'                       => __('Tree -> Grafting'),
            'TreesView.rootstock'                      => __('Tree -> Rootstock'),
            'TreesView.experiment_site'                => __('Tree -> Experiment Site'),
            'VarietiesView.id'                         => __('Varieties -> Id'),
            'VarietiesView.convar'                     => __('Varieties -> Convar'),
            'VarietiesView.official_name'              => __('Varieties -> Official Name'),
            'VarietiesView.acronym'                    => __('Varieties -> Acronym'),
            'VarietiesView.plant_breeder'              => __('Varieties -> Plant Breeder'),
            'VarietiesView.registration'               => __('Varieties -> Registration'),
            'VarietiesView.description'                => __('Varieties -> Description'),
            'VarietiesView.batch_id'                   => __('Varieties -> Batch Id'),
        ];
        
        if ( ! key_exists($key, $translations)) {
            throw new Exception("Field translation not found: $key");
        }
        
        return $translations[$key];
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
    public function patchEntityWithQueryData($entity, $request)
    {
        $data['root_view'] = $request['root_view'];
        unset($request['root_view']);
        
        $views = array_keys($this->getViewNames());
        $query = array();
        
        foreach ($views as $view) {
            $query[$view] = $request[$view];
            unset($request[$view]);
        }
        
        $data['fields']   = $query;
        $request['query'] = json_encode($data);
        
        return $this->patchEntity($entity, $request);
    }
    
    /**
     * Return associative array with names of the views to query as keys and its translated names as values
     *
     * @return array
     */
    public function getViewNames()
    {
        return [
            'BatchesView'       => __('Batches'),
            'CrossingsView'     => __('Crossings'),
            'MarksView'         => __('Marks'),
            'MotherTreesView'   => __('Mother Trees'),
            'ScionsBundlesView' => __('Scions Bundles'),
            'TreesView'         => __('Trees'),
            'VarietiesView'     => __('Varieties'),
        ];
    }
    
    /**
     * Return columns from given query data with dot noted key and translated value
     *
     * The $query_data must be a stdClass with at least the properties:
     * - fields : result of the query builder field selection as stdClass
     *
     * @param $query_data
     *
     * @return array
     */
    public function getViewQueryColumns($query_data)
    {
        $tmp = $this->getActiveFields($query_data);
        $tmp = $this->_getTranslatedFieldsFromList($tmp);
        
        // get recursively dot notated keys
        $return = [];
        foreach ($tmp as $key => $value) {
            $dot_key          = $this->_getDottedFieldPath($key);
            $return[$dot_key] = $value;
        }
        
        return $return;
    }
    
    /**
     * Return active fields from given query data.
     *
     * The $query_data must be a stdClass with at least the properties:
     * - fields : result of the query builder field selection as stdClass
     *
     * @param $query_data
     *
     * @return array
     */
    public function getActiveFields($query_data)
    {
        return $this->_parseQuery($query_data->fields, 'field');
    }
    
    /**
     * Return array with fields or tables
     *
     * @param \stdClass $query
     * @param string $type
     *
     * @return array
     */
    private function _parseQuery(\stdClass $query, string $type): array
    {
        $return = array();
        $views  = array_keys($this->getViewNames());
        
        foreach ($views as $view) {
            foreach ($query->$view as $key => $value) {
                if ($value) {
                    if ('table' == $type) {
                        // tables
                        $return[] = $view;
                    } else {
                        // fields
                        $return[] = $view . '.' . $key;
                    }
                }
            }
        }
        
        return array_unique($return);
    }
    
    /**
     * Return associative array with view.field as key and its translated name as value.
     *
     * @param array $list
     *
     * @return array
     */
    private function _getTranslatedFieldsFromList(array $list)
    {
        $fields = [];
        foreach ($list as $item) {
            $fields[$item] = $this->translateFields($item);
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
    private function _getDottedFieldPath(string $key): string
    {
        $table = explode('.', $key)[0];
        foreach ($this->viewQueryAssociations as $association) {
            $path = $this->viewQueryRoot . '.' . $association;
            $pos  = strpos($path, $table);
            if ($pos) {
                return substr($path, 0, $pos) . $key;
            }
        }
        
        return $key;
    }
    
    /**
     * Return query from given query data.
     *
     * The $query_data must be a stdClass with at least the properties:
     * - root_view : the FROM table
     * - fields : result of the query builder field selection as stdClass
     *
     * @param $query_data
     *
     * @return Query
     */
    public function buildViewQuery($query_data)
    {
        $tables = $this->_parseQuery($query_data->fields, 'table');
        
        // keep in memory for later use
        $this->viewQueryRoot         = $query_data->root_view;
        $associations                = $this->_buildAssociationsForContainStatement($this->viewQueryRoot, $tables);
        $this->viewQueryAssociations = $associations;
        
        $rootTable = TableRegistry::get($this->viewQueryRoot);
        $query     = $rootTable
            ->find('all')
            ->contain($associations);
        
        return $query;
    }
    
    /**
     * Return 1-dim array with association paths of $tables in dot notation starting from root.
     *
     * @param string $root
     * @param array $tables
     *
     * @return array
     */
    private function _buildAssociationsForContainStatement(string $root, array $tables)
    {
        $associations = $this->_getAssociationsRecursive($root, $tables);
        
        if (empty($associations)) {
            return [];
        }
        
        return $this->_getDottedArrayPath($associations);
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
    private function _getAssociationsRecursive(string $root, array $tables)
    {
        $tables = array_diff($tables, [$root]);
        
        if (empty($tables)) {
            return null;
        }
        
        $associations = array();
        $return       = array();
        
        foreach ($this->getAssociationsOf($root) as $association) {
            $key = array_search($association, $tables);
            if (false !== $key) {
                $associations[] = $tables[$key];
                unset($tables[$key]);
            }
        }
        
        if (empty($associations)) {
            return null;
        }
        
        foreach ($associations as $key => $association) {
            $return[$association] = $this->_getAssociationsRecursive($association, $tables);
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
    public function getAssociationsOf(string $table_name, $hasManyOnly = false)
    {
        $associated = array();
        
        $tmp = TableRegistry::get($table_name);
        $has = $tmp->associations();
        
        $tables = array();
        foreach ($has as $table => $properties) {
            $tables[] = $table;
        }
        
        if ($hasManyOnly) {
            foreach ($tables as $table) {
                if ( ! $has->get($table) instanceof \Cake\ORM\Association\HasMany) {
                    unset($tables[array_search($table, $tables)]);
                }
            }
        }
        
        foreach ($tables as $table) {
            $associated[] = $has->get($table)->name();
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
    private function _getDottedArrayPath($array): array
    {
        $return = [];
        foreach ($array as $base => $child) {
            if ( ! is_array($child)) {
                $return[] = $base;
            } else {
                $return[] = trim($base . '.' . implode('.', $this->_getDottedArrayPath($child)), '.');
            }
        }
        
        return $return;
    }
    
    /**
     * Return data for the query where builder
     *
     * The $query_data must be a stdClass with at least the properties:
     * - fields : result of the query builder field selection as stdClass
     *
     * @param $query_data
     *
     * @return array
     */
    public function getFilterData($query_data)
    {
        $tables          = $this->getActiveViewTables($query_data);
        $tables_fields[] = $this->getFieldTypeMapOf($tables);
        
        $fields = [];
        foreach ($tables_fields as $table => $table_fields) {
            foreach ($tables_fields as $field => $type) {
                $fields[$table . '.' . $field] = $this->_getFieldFilterData($table, $field, $type);
            }
        }
        
        debug($fields);
    }
    
    /**
     * Return active tables from given query data.
     *
     * The $query_data must be a stdClass with at least the properties:
     * - fields : result of the query builder field selection as stdClass
     *
     * @param $query_data
     *
     * @return array
     */
    public function getActiveViewTables($query_data)
    {
        return $this->_parseQuery($query_data->fields, 'table');
    }
    
    private function _getFieldFilterData(string $table, string $field, string $type)
    {
        $data['id']    = $table . '.' . $field;
        $data['label'] = $this->translateFields($data['id']);
        $data['type']  = $this->_getFieldTypeForFilter($type);
        
        // ToDo: continue here: http://querybuilder.js.org/index.html#filters
    }
    
    /**
     * Return the type our query where builder understands from given cakeish db type
     *
     * @param string $type
     *
     * @return string
     */
    private function _getFieldTypeForFilter(string $type): string
    {
        $cast = [
            'string'       => 'string',
            'text'         => 'string',
            'integer'      => 'integer',
            'smallinteger' => 'integer',
            'tinyinteger'  => 'integer',
            'biginteger'   => 'integer',
            'float'        => 'double',
            'decimal'      => 'double',
            'boolean'      => 'boolean',
            'date'         => 'date',
            'datetime'     => 'datetime',
            'timestamp'    => 'integer',
            'time'         => 'time',
        ];
        
        return $cast[$type];
    }
}
