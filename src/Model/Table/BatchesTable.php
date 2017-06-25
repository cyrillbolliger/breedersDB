<?php

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use SoftDelete\Model\Table\SoftDeleteTrait;
use App\Model\Rule\IsNotReferredBy;


/**
 * Batches Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Crossings
 * @property \Cake\ORM\Association\HasMany $Marks
 * @property \Cake\ORM\Association\HasMany $Varieties
 *
 * @method \App\Model\Entity\Batch get($primaryKey, $options = [])
 * @method \App\Model\Entity\Batch newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Batch[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Batch|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Batch patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Batch[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Batch findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class BatchesTable extends Table
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
        
        $this->table('batches');
        $this->displayField('code');
        $this->primaryKey('id');
        
        $this->addBehavior('Timestamp');
        $this->addBehavior('Printable');
        
        $this->belongsTo('Crossings', [
            'foreignKey' => 'crossing_id',
            'joinType'   => 'INNER'
        ]);
        $this->hasMany('Marks', [
            'foreignKey' => 'batch_id'
        ]);
        $this->hasMany('Varieties', [
            'foreignKey' => 'batch_id'
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
            ->add('code', 'custom', [
                'rule'    => function ($value, $context) {
                    return (bool)preg_match('/^(\d{2}[A-Z]|000)$/', $value);
                },
                'message' => __('Input not valid. The code must match the following pattern: "NumberNumberUppercaseletter". Example: 17A'),
            ]);
        
        $validator
            ->localizedTime('date_sowed', 'date')
            ->allowEmpty('date_sowed');
        
        $validator
            ->integer('numb_seeds_sowed')
            ->allowEmpty('numb_seeds_sowed');
        
        $validator
            ->integer('numb_sprouts_grown')
            ->allowEmpty('numb_sprouts_grown');
        
        $validator
            ->allowEmpty('seed_tray');
        
        $validator
            ->localizedTime('date_planted', 'date')
            ->allowEmpty('date_planted');
        
        $validator
            ->integer('numb_sprouts_planted')
            ->allowEmpty('numb_sprouts_planted');
        
        $validator
            ->allowEmpty('patch');
        
        $validator
            ->allowEmpty('note');
        
        $validator
            ->integer('crossing_id')
            ->notEmpty('crossing_id');
        
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
        $rules->add($rules->isUnique(
            ['code', 'crossing_id'],
            __('A batch with this code and this crossing already exists.')
        ));
        $rules->add($rules->existsIn(['crossing_id'], 'Crossings'));
        
        $rules->addDelete(new IsNotReferredBy(['Varieties' => 'batch_id']), 'isNotReferredBy');
        $rules->addDelete(new IsNotReferredBy(['Marks' => 'batch_id']), 'isNotReferredBy');
        
        return $rules;
    }
    
    /**
     * Return list with the id of the batch as key and crossing_code.batches_code as value
     * filtered by the given search term
     *
     * @param string $term
     *
     * @return Cake\ORM\Query
     */
    public function searchCrossingBatchs(string $term)
    {
        $query = $this->find('list')->innerJoinWith('Crossings');
        
        $concat = $query->func()->concat([
            'Crossings.code' => 'identifier',
            '.',
            'Batches.code'   => 'identifier',
        ]);
        
        $query->select([
            'id',
            'code' => $concat
        ]);
        
        $search = explode('.', trim($term));
        if (1 < count($search)) {
            $query->where(['Crossings.code LIKE' => '%' . $search[0] . '%'])
                  ->andWhere(['Batches.code LIKE' => '%' . $search[1] . '%']);
        } else {
            $query->where(['Crossings.code LIKE' => '%' . $search[0] . '%']);
        }
        
        return $query;
    }
    
    /**
     * Return query filtered by given search term searching the convar
     *
     * @param string $term
     *
     * @return Cake\ORM\Query
     */
    public function filterCrossingBatches(string $term)
    {
        $list = $this->searchCrossingBatchs($term)->toArray();
        $ids  = array_keys($list);
        
        // if nothing was found
        if (empty($ids)) {
            return null;
        }
        
        return $this->find()
                    ->contain('Crossings')
                    ->where(['Batches.id IN' => $ids]);
    }
    
    public function getCrossingBatchList(int $id)
    {
        $batch = $this->get($id, ['contain' => 'Crossings']);
        
        return [$id => $batch->crossing_batch];
    }
    
    /**
     * Return label to print in Zebra Printing Language
     *
     * @param int $id
     *
     * @return string
     */
    public function getLabelZpl(int $id)
    {
        $batch       = $this->get($id, ['contain' => ['Crossings']]);
        $description = $batch->crossing_batch;
        
        return $this->getZPL($description);
        
    }
}
