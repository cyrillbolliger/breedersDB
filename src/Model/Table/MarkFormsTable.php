<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use App\Model\Rule\IsNotReferredBy;


/**
 * MarkForms Model
 *
 * @property \Cake\ORM\Association\HasMany $MarkFormFields
 * @property \Cake\ORM\Association\HasMany $Marks
 *
 * @method \App\Model\Entity\MarkForm get($primaryKey, $options = [])
 * @method \App\Model\Entity\MarkForm newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\MarkForm[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MarkForm|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MarkForm patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\MarkForm[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\MarkForm findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MarkFormsTable extends Table
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

        $this->table('mark_forms');
        $this->displayField('name');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('MarkFormFields', [
            'foreignKey' => 'mark_form_id'
        ]);
        $this->hasMany('Marks', [
            'foreignKey' => 'mark_form_id'
        ]);
        $this->belongsToMany('MarkFormProperties', [
            'joinTable' => 'MarkFormFields'
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
            ->requirePresence('name', 'create')
            ->notEmpty('name')
            ->add('name', 'unique', [
                'rule' => 'validateUnique',
                'provider' => 'table',
                'message' => __('This name has already been used.'),
            ]);

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

        $rules->addDelete(new IsNotReferredBy(['Marks' => 'mark_form_id']),'isNotReferredBy');
        $rules->addDelete(new IsNotReferredBy(['MarkFormFields' => 'mark_form_id']),'isNotReferredBy');

        return $rules;
    }

    /**
     * Return query filtered by given search term searching the name
     *
     * @param string $term
     * @return Cake\ORM\Query
     */
    public function filter(string $term) {
        $where = trim($term);

        $query = $this->find()
                ->where(['name LIKE' => '%'.$where.'%']);

        return $query;
    }
}
