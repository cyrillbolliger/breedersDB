<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MotherTreesView Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Trees
 * @property \Cake\ORM\Association\BelongsTo $Crossings
 *
 * @method \App\Model\Entity\MotherTreesView get($primaryKey, $options = [])
 * @method \App\Model\Entity\MotherTreesView newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\MotherTreesView[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MotherTreesView|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MotherTreesView patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\MotherTreesView[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\MotherTreesView findOrCreate($search, callable $callback = null, $options = [])
 */
class MotherTreesViewTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('mother_trees_view');

        $this->belongsTo('Trees', [
            'foreignKey' => 'tree_id'
        ]);
        $this->belongsTo('Crossings', [
            'foreignKey' => 'crossing_id',
            'joinType' => 'INNER'
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
            ->requirePresence('id', 'create')
            ->notEmpty('id');

        $validator
            ->requirePresence('crossing', 'create')
            ->notEmpty('crossing');

        $validator
            ->requirePresence('code', 'create')
            ->notEmpty('code');

        $validator
            ->boolean('planed')
            ->requirePresence('planed', 'create')
            ->notEmpty('planed');

        $validator
            ->date('date_pollen_harvested')
            ->allowEmpty('date_pollen_harvested');

        $validator
            ->date('date_impregnated')
            ->allowEmpty('date_impregnated');

        $validator
            ->date('date_fruit_harvested')
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

        $validator
            ->requirePresence('publicid', 'create')
            ->notEmpty('publicid');

        $validator
            ->numeric('offset')
            ->allowEmpty('offset');

        $validator
            ->allowEmpty('row');

        $validator
            ->requirePresence('experiment_site', 'create')
            ->notEmpty('experiment_site');

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
        $rules->add($rules->existsIn(['tree_id'], 'Trees'));
        $rules->add($rules->existsIn(['crossing_id'], 'Crossings'));

        return $rules;
    }
}