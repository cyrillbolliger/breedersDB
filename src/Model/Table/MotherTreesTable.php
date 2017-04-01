<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use SoftDelete\Model\Table\SoftDeleteTrait;


/**
 * MotherTrees Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Trees
 * @property \Cake\ORM\Association\BelongsTo $Crossings
 *
 * @method \App\Model\Entity\MotherTree get($primaryKey, $options = [])
 * @method \App\Model\Entity\MotherTree newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\MotherTree[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MotherTree|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MotherTree patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\MotherTree[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\MotherTree findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MotherTreesTable extends Table
{
    use SoftDeleteTrait;
    
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('mother_trees');
        $this->displayField('code');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Crossings', [
            'foreignKey' => 'crossing_id'
        ]);
        $this->belongsTo('Trees', [
            'foreignKey' => 'tree_id'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
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
            ->notEmpty('code');
    
        $validator
            ->integer('tree_id')
            ->requirePresence('tree_id', 'create', __('The publicid must not be empty.'));
        
        $validator
            ->integer('crossing_id')
            ->requirePresence('crossing_id')
            ->add('crossing_id', 'custom', [
                'rule' => function($value, $context) {
                    $crossing = $this->Crossings->get($value);
                    $tree = $this->Trees->get($context['data']['tree_id']);
                    if ( empty($crossing->mother_variety_id) || empty($tree->variety_id)) {
                        // there is nothing to validate
                        return true;
                    }
                    return $crossing->mother_variety_id === $tree->variety_id;
                },
                'message' => __('The variety of the mother tree must match the mother variety of the crossing.'),
            ]);
                
        $validator
            ->boolean('planed')
            ->requirePresence('planed', 'create')
            ->notEmpty('planed');

        $validator
            ->localizedTime('date_pollen_harvested', 'date')
            ->allowEmpty('date_pollen_harvested');

        $validator
            ->localizedTime('date_impregnated', 'date')
            ->allowEmpty('date_impregnated');

        $validator
            ->localizedTime('date_fruit_harvested', 'date')
            ->allowEmpty('date_fruit_harvested');

        $validator
            ->integer('numb_portions')
            ->allowEmpty('numb_portions');

        $validator
            ->integer('numb_flowers')
            ->allowEmpty('numb_flowers');

        $validator
            ->integer('numb_seeds')
            ->allowEmpty('numb_seeds');

        $validator
            ->allowEmpty('target');

        $validator
            ->allowEmpty('note');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->isUnique(['id']));
        $rules->add($rules->isUnique(['code'], __('This code has already been used. Please use a unique code.')));
        $rules->add($rules->existsIn(['tree_id'], 'Trees'));
        $rules->add($rules->existsIn(['crossing_id'], 'Crossings'));
        
        return $rules;
    }
    
    /**
     * Return query filtered by given search term searching the code
     * 
     * @param string $term
     * @return Cake\ORM\Query
     */
    public function filterCodes(string $term) {
        
        $publicid = $this->Trees->fillPublicId($term);
        
        return $this->find()
                ->contain(['Trees'])
                ->where(['MotherTrees.code LIKE' => $term.'%'])
                ->orWhere(['Trees.publicid' => $publicid]);
    }
}
