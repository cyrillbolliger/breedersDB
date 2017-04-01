<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Event\Event;
use ArrayObject;
use SoftDelete\Model\Table\SoftDeleteTrait;
use App\Model\Rule\IsNotReferredBy;

/**
 * Trees Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Varieties
 * @property \Cake\ORM\Association\BelongsTo $Rootstocks
 * @property \Cake\ORM\Association\BelongsTo $Graftings
 * @property \Cake\ORM\Association\BelongsTo $Rows
 * @property \Cake\ORM\Association\BelongsTo $ExperimentSites
 * @property \Cake\ORM\Association\HasMany $Marks
 *
 * @method \App\Model\Entity\Tree get($primaryKey, $options = [])
 * @method \App\Model\Entity\Tree newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Tree[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Tree|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Tree patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Tree[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Tree findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class TreesTable extends Table
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

        $this->table('trees');
        $this->displayField('publicid');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Varieties', [
            'foreignKey' => 'variety_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Rootstocks', [
            'foreignKey' => 'rootstock_id'
        ]);
        $this->belongsTo('Graftings', [
            'foreignKey' => 'grafting_id'
        ]);
        $this->belongsTo('Rows', [
            'foreignKey' => 'row_id'
        ]);
        $this->belongsTo('ExperimentSites', [
            'foreignKey' => 'experiment_site_id'
        ]);
        $this->hasMany('Marks', [
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
            ->requirePresence('publicid', 'create')
            ->notEmpty('publicid')
            ->add('publicid', 'unique', [
                'rule' => 'validateUnique', 
                'provider' => 'table',
                'message' => __('This number has already been used.'),
            ])
            ->add('publicid', 'custom', [
                'rule' => function($value, $context) {
                    return (bool) preg_match('/^#?\d{8}$/', $value);
                },
                'message' => __('Input not valid. The publicid must only contain numbers.'),
            ]);

        $validator
            ->localizedTime('date_grafted', 'date')
            ->allowEmpty('date_grafted');

        $validator
            ->localizedTime('date_planted', 'date')
            ->allowEmpty('date_planted');

        $validator
            ->localizedTime('date_eliminated', 'date')
            ->allowEmpty('date_eliminated');

        $validator
            ->boolean('genuine_seedling')
            ->notEmpty('genuine_seedling');

        $validator
            ->boolean('migrated_tree')
            ->notEmpty('migrated_tree');

        $validator
            ->numeric('offset')
            ->allowEmpty('offset');

        $validator
            ->allowEmpty('note');
        
        $validator
            ->integer('experiment_site_id')    
            ->requirePresence('experiment_site_id', 'create')
            ->notEmpty('experiment_site_id');
        
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
        $rules->add($rules->isUnique(['publicid']));
        $rules->add($rules->existsIn(['variety_id'], 'Varieties'));
        $rules->add($rules->existsIn(['rootstock_id'], 'Rootstocks'));
        $rules->add($rules->existsIn(['grafting_id'], 'Graftings'));
        $rules->add($rules->existsIn(['row_id'], 'Rows'));
        $rules->add($rules->existsIn(['experiment_site_id'], 'ExperimentSites'));
        
        $rules->addDelete(new IsNotReferredBy(['Crossings' => 'mother_tree_id']),'isNotReferredBy');
        $rules->addDelete(new IsNotReferredBy(['Marks' => 'tree_id']),'isNotReferredBy');
        
        return $rules;
    }
    
    public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
    {

        // fill public id with leading zeros
        if ( isset( $data['publicid']) ) {
            $data['publicid'] = $this->_fillPublicId($data['publicid']);
        }
        
        // set experiment site
        if ( ! isset( $data['experiment_site_id'] )) {
            $data['experiment_site_id'] = $options['experiment_site_id'];
        }
        
        // create new variety if crossing_batch was given instead of variety_id
        if ( isset( $data['variety_id'] ) ) {
            if ( preg_match('/^[a-zA-Z0-9]{4,8}\.\d{2}[A-Z]$/', $data['variety_id']) ) {
                $data['variety_id'] = $this->Varieties->addNewFromCrossingBatch($data['variety_id']);
            }
        }
    }
    
    /**
     * prefix the public id with a # if elimination date was set
     * 
     * @param int $id
     * @param array $data
     * @return array
     */
    public function prefixPublicidOnElimination(int $id, array $data) {
        if ( ! empty( $data['date_eliminated'] ) ) {
            if ( ! isset($data['publicid'])) {
                $data['publicid'] = $this->get($id)->publicid;
            }
            if ( 0 !== strpos($data['publicid'], '#') ) {
                $data['publicid'] = '#' . $data['publicid'];
            }
        }
        return $data;
    }
    
    /**
     * Return tree query of tree with given publicid
     * 
     * @param string $publicid
     * @return Cake\ORM\Query
     */
    public function getByPublicId(string $publicid) {
        $publicid = $this->_fillPublicId($publicid);
        
        return $this->find()
                ->contain(['Varieties', 'Rootstocks', 'Graftings', 'Rows', 'ExperimentSites'])
                ->where(['publicid'=>$publicid])
                ->first();
    }
    
    /**
     * Return query filtered by given search term searching the convar and publicid
     * 
     * @param string $term
     * @return Cake\ORM\Query
     */
    public function filter(string $term) {
        // if not a public id
        if ( preg_match('/\.|[a-zA-z]|.{9,}/', $term) ) {
            // set publicid to false
            $publicid = false;
        } else {
            // if publicid
            $publicid = $this->_fillPublicId($term);
        }
        
        $varieties = $this->Varieties->searchConvars($term)->toArray();
        $variety_ids = array_keys($varieties);
        
        $where = array();
        if ( $publicid ) {
            $where[] = ['publicid'=>$publicid];
        }
        
        if ( ! empty( $variety_ids ) ) {
            $where[] = ['variety_id IN'=>$variety_ids];
        }
        
        // if nothing was found
        if ( empty( $where ) ) {
            return null;
        }
        
        $query = $this->find()
                ->contain(['Varieties', 'Rootstocks', 'Graftings', 'Rows', 'ExperimentSites'])
                ->where($where[0]);
        
        if ( 2 == count($where) ) {
            $query->orWhere($where[1]);
        }
                
        return $query;
    }
    
    /**
     * fills up missing zeros in given publicid
     * 
     * @param string $publicid
     * @return string
     */
    protected function _fillPublicId(string $publicid ) {
        // if publicid doesn't contain a #
        if ( 0 !== strpos($publicid, '#') ) {
            return sprintf('%08d', $publicid);
        } else {
            return '#' . sprintf('%08d', substr($publicid,1));
        }
    }
    
    /**
     * return an array with the id as key and 'publicid (convar)' as value
     * 
     * @param int $id
     * @return array|null
     */
    public function getIdPublicidAndConvarList(int $id) {
        $tree = $this->get($id, ['contain'=>['Varieties']]);
        
        if ($tree) {
            return [$id => $tree->publicid .' ('.$tree->convar.')'];
        } else {
            return null;
        }
    }
    
    /**
     * Return convar of tree by given id
     * 
     * @param int $id
     * @return string
     */
    public function getConvar(int $id) {
        $tree = $this->get($id, ['contain'=>['Varieties']]);
        
        return $tree->convar;
    }
}
