<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use SoftDelete\Model\Table\SoftDeleteTrait;
use App\Model\Rule\IsNotReferredBy;


/**
 * Crossings Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Varieties
 * @property \Cake\ORM\Association\BelongsTo $Varieties
 * @property \Cake\ORM\Association\BelongsTo $Trees
 * @property \Cake\ORM\Association\HasMany $Batches
 *
 * @method \App\Model\Entity\Crossing get($primaryKey, $options = [])
 * @method \App\Model\Entity\Crossing newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Crossing[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Crossing|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Crossing patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Crossing[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Crossing findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CrossingsTable extends Table
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

        $this->table('crossings');
        $this->displayField('code');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Varieties', [
            'foreignKey' => 'mother_variety_id'
        ]);
        $this->belongsTo('Varieties', [
            'foreignKey' => 'father_variety_id'
        ]);
        $this->belongsTo('Trees', [
            'foreignKey' => 'mother_tree_id'
        ]);
        $this->hasMany('Batches', [
            'foreignKey' => 'crossing_id'
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
            ->notEmpty('code')
            ->add('code', 'custom', [
                'rule' => function($value, $context) {
                    return (bool) preg_match('/^[a-zA-Z0-9]{4,8}$/', $value);
                },
                'message' => __('Input not valid. The code must only contain alphanumerical characters (whitout umlauts). Length between four and aight characters.'),
            ]);
                
        $validator
            ->integer('mother_tree_id')
            ->allowEmpty('mother_tree_id')
            ->add('mother_tree_id', 'custom', [
                'rule' => function($value, $context) {
                    if ( empty($context['data']['mother_variety_id'] ) ) {
                        // well if there is no mother variety there is nothing to validate
                        return true;
                    }
                    $tree_convar = $this->Trees->getConvar($value);
                    $variety_convar = $this->Varieties->getConvar($context['data']['mother_variety_id']);
                    return $tree_convar === $variety_convar;
                },
                'message' => __('The convar of the mother tree must match the convar of the mother variety.'),
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
        $rules->add($rules->existsIn(['mother_variety_id'], 'Varieties'));
        $rules->add($rules->existsIn(['father_variety_id'], 'Varieties'));
        $rules->add($rules->existsIn(['mother_tree_id'], 'Trees'));
        
        $rules->addDelete(new IsNotReferredBy(['Batches' => 'crossing_id']),'isNotReferredBy');

        return $rules;
    }
    
    /**
     * Return query filtered by given search term searching the code
     * 
     * @param string $term
     * @return Cake\ORM\Query
     */
    public function filterCodes(string $term) {
        
        return $this->find()
                ->where(['code LIKE' => $term.'%']);
    }
}
