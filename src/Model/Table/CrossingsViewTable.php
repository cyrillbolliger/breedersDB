<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CrossingsView Model
 *
 * @property \Cake\ORM\Association\BelongsTo $VarietiesView
 * @property \Cake\ORM\Association\HasMany $MotherTreesView
 * @property \Cake\ORM\Association\HasMany $BatchesView
 *
 * @method \App\Model\Entity\CrossingsView get($primaryKey, $options = [])
 * @method \App\Model\Entity\CrossingsView newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\CrossingsView[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CrossingsView|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CrossingsView patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\CrossingsView[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\CrossingsView findOrCreate($search, callable $callback = null, $options = [])
 */
class CrossingsViewTable extends Table
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

        $this->table('crossings_view');
        $this->displayField('code');
        $this->primaryKey('id');
        
        $this->hasMany('BatchesView', [
            'foreignKey' => 'crossing_id'
        ]);
        $this->hasMany('MotherTreesView', [
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
            ->requirePresence('id', 'create')
            ->notEmpty('id');

        $validator
            ->requirePresence('code', 'create')
            ->notEmpty('code');

        $validator
            ->allowEmpty('mother_variety');

        $validator
            ->allowEmpty('father_variety');

        return $validator;
    }
}
