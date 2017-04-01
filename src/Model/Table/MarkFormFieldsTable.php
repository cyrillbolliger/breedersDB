<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MarkFormFields Model
 *
 * @property \Cake\ORM\Association\BelongsTo $MarkForms
 * @property \Cake\ORM\Association\BelongsTo $MarkFormProperties
 *
 * @method \App\Model\Entity\MarkFormField get($primaryKey, $options = [])
 * @method \App\Model\Entity\MarkFormField newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\MarkFormField[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MarkFormField|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MarkFormField patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\MarkFormField[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\MarkFormField findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MarkFormFieldsTable extends Table
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

        $this->table('mark_form_fields');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('MarkForms', [
            'foreignKey' => 'mark_form_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('MarkFormProperties', [
            'foreignKey' => 'mark_form_property_id',
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
            ->allowEmpty('id', 'create')
            ->add('id', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->integer('priority')
            ->requirePresence('priority', 'create')
            ->notEmpty('priority');

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
        $rules->add($rules->existsIn(['mark_form_id'], 'MarkForms'));
        $rules->add($rules->existsIn(['mark_form_property_id'], 'MarkFormProperties'));

        return $rules;
    }
}
