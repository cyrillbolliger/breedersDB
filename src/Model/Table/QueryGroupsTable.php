<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * QueryGroups Model
 *
 * @property \Cake\ORM\Association\HasMany $Queries
 *
 * @method \App\Model\Entity\QueryGroup get($primaryKey, $options = [])
 * @method \App\Model\Entity\QueryGroup newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\QueryGroup[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\QueryGroup|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\QueryGroup patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\QueryGroup[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\QueryGroup findOrCreate($search, callable $callback = null, $options = [])
 */
class QueryGroupsTable extends Table
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

        $this->table('query_groups');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->hasMany('Queries', [
            'foreignKey' => 'query_group_id'
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
            ->add('code', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

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
        $rules->add($rules->isUnique(['code']));

        return $rules;
    }
}
