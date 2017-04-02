<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use SoftDelete\Model\Table\SoftDeleteTrait;
use App\Model\Rule\IsNotReferredBy;


/**
 * Varieties Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Batches
 * @property \Cake\ORM\Association\HasMany $ScionsBundles
 * @property \Cake\ORM\Association\HasMany $Trees
 *
 * @method \App\Model\Entity\Variety get($primaryKey, $options = [])
 * @method \App\Model\Entity\Variety newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Variety[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Variety|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Variety patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Variety[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Variety findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class VarietiesTable extends Table
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

        $this->table('varieties');
        $this->displayField('code');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Batches', [
            'foreignKey' => 'batch_id',
            'joinType' => 'INNER'
        ]);
        $this->hasMany('ScionsBundles', [
            'foreignKey' => 'variety_id'
        ]);
        $this->hasMany('Trees', [
            'foreignKey' => 'variety_id'
        ]);
        $this->hasMany('Marks', [
            'foreignKey' => 'variety_id'
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
            ->allowEmpty('official_name');

        $validator
            ->allowEmpty('plant_breeder');

        $validator
            ->allowEmpty('registration');

        $validator
            ->allowEmpty('description');

        $validator
            ->boolean('deleted');

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
        $rules->add($rules->existsIn(['batch_id'], 'Batches'));
        $rules->add($rules->isUnique(
            ['code', 'batch_id'],
            __('This code has already been used.')
        ));
        
        $rules->addDelete(new IsNotReferredBy(['Trees' => 'variety_id']),'isNotReferredBy');
        $rules->addDelete(new IsNotReferredBy(['ScionsBundles' => 'variety_id']),'isNotReferredBy');
        $rules->addDelete(new IsNotReferredBy(['Crossings' => 'mother_variety_id']),'isNotReferredBy');
        $rules->addDelete(new IsNotReferredBy(['Crossings' => 'father_variety_id']),'isNotReferredBy');
        $rules->addDelete(new IsNotReferredBy(['Marks' => 'variety_id']),'isNotReferredBy');
        
        return $rules;
    }
    
    /**
     * Return next unused code number for a breeder variety with the given batch_id
     * 
     * @param int $batch_id
     * @return string
     */
    public function getNextFreeCode(int $batch_id) {
        $query = $this->find()
                ->where(['batch_id'=>$batch_id])
                ->order(['code'=>'DESC'])
                ->first();
        
        if ( empty($query->code) ){
            $return = '001';
        } else {
            $return = (string) sprintf('%03d', (int) $query->code + 1);
        }
        
        return $return;
    }
    
    /**
     * Return list with the id of the variety as key and the convar as value
     * filtered by the given search term
     * 
     * @param string $term
     * @return Cake\ORM\Query
     */
    public function searchConvars(string $term) {
        $query = $this->find('list')->contain([
            'Batches',
            'Batches.Crossings',
        ]);
        
        $concat = $query->func()->concat([
            'Crossings.code' => 'identifier',
            '.',
            'Batches.code' => 'identifier',
            '.',
            'Varieties.code' => 'identifier'
        ]);
        
        $query->select([
                    'Varieties.id',
                    'code' => $concat
                ]);

        $search = explode('.', trim($term));
        if ( 3 === count($search) ) {
            $query->where(['Crossings.code LIKE' => '%'.$search[0].'%'])
                    ->andWhere(['Batches.code LIKE' => '%'.$search[1].'%'])
                    ->andWhere(['Varieties.code LIKE' => '%'.$search[2].'%']);
        } elseif ( 2 === count($search) ) {
            $query->where(['Crossings.code LIKE' => '%'.$search[0].'%'])
                    ->andWhere(['Batches.code LIKE' => '%'.$search[1].'%']);
        } else {
            $query->where(['Crossings.code LIKE' => '%'.$search[0].'%']);
        }
        
        return $query;
    }
    
    /**
     * Return query filtered by given search term searching the convar
     * 
     * @param string $term
     * @return Cake\ORM\Query
     */
    public function filterConvars(string $term) {
        $list = $this->searchConvars($term)->toArray();
        $ids = array_keys($list);
        
        // if nothing was found
        if ( empty($ids) ) {
            return null;
        }
        
        return $this->find()
                ->contain('Batches')
                ->where(['Varieties.id IN' => $ids]);
    }
    
    /**
     * get id => convar list from given id
     * 
     * @param int $id
     * @return array
     */
    public function getConvarList(int $id) {
        $variety = $this->get($id, [
            'contain' => ['Batches', 'Batches.Crossings'],
            'fields' => ['id', 'Varieties.code', 'Batches.code', 'Crossings.code']
        ]);
        
        $varieties = [
            [
                $id => $variety->batch->crossing->code .'.'. $variety->batch->code .'.'. $variety->code,
            ],
        ];
        
        return $varieties;
    }
    
    /**
     * Add a new variety from the given crossing batch string
     * 
     * @param string $crossing_batch
     * @return boolean
     */
    public function addNewFromCrossingBatch(string $crossing_batch) {
        // get batch
        $batch = $this->Batches->searchCrossingBatchs($crossing_batch)->toArray();
        $batch_id = key($batch);
        // get next free variety code
        $code = $this->getNextFreeCode($batch_id);

        // create entity
        $variety = $this->newEntity();
        $variety->code = $code;
        $variety->batch_id = $batch_id;

        // persist variety
        if ($this->save($variety)) {
            return $variety->id;
        } else {
            return false;
        }
    }
    
    /**
     * Return convar by given id
     * 
     * @param int $id
     * @return string
     */
    public function getConvar(int $id) {
        $variety = $this->get($id, ['contain'=>['Batches']]);
        
        return $variety->convar;
    }
}