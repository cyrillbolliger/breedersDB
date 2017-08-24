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
     * @throws Exception if given key doesn't exist in translations array
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
            throw new \Cake\Core\Exception\Exception("Field translation not found: $key");
        }
        
        return $translations[$key];
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
     * Return array of associations from given table
     *
     * @param string $table_name
     *
     * @return array of associations
     */
    public function getAssociationsOf(string $table_name)
    {
        $associated = array();
        
        $tmp = TableRegistry::get($table_name);
        $has   = $tmp->associations();
        
        foreach ($has as $table => $properties) {
            // use the way over the reflection class to retrive the camel cased name
            $reflection = new \ReflectionClass(TableRegistry::get($table));
            $associated[] = $reflection->getShortName();
        }
        
        return $associated;
    }
}
