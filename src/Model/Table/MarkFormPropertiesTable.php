<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use App\Model\Rule\IsNotReferredBy;
use Cake\Event\Event;
use ArrayObject;
use Cake\Database\Schema\Table as Schema;



/**
 * MarkFormProperties Model
 *
 * @property \Cake\ORM\Association\BelongsTo $MarkFormPropertyTypes
 * @property \Cake\ORM\Association\HasMany $MarkFormFields
 * @property \Cake\ORM\Association\HasMany $MarkValues
 *
 * @method \App\Model\Entity\MarkFormProperty get($primaryKey, $options = [])
 * @method \App\Model\Entity\MarkFormProperty newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\MarkFormProperty[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MarkFormProperty|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MarkFormProperty patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\MarkFormProperty[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\MarkFormProperty findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MarkFormPropertiesTable extends Table
{

    protected function _initializeSchema(Schema $schema)
    {
        $schema->columnType('validation_rule', 'json');
        return $schema;
    }
    
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('mark_form_properties');
        $this->displayField('name');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('MarkFormPropertyTypes', [
            'foreignKey' => 'mark_form_property_type_id',
            'joinType' => 'INNER'
        ]);
        $this->hasMany('MarkFormFields', [
            'foreignKey' => 'mark_form_property_id'
        ]);
        $this->hasMany('MarkValues', [
            'foreignKey' => 'mark_form_property_id'
        ]);
        $this->hasMany('MarkScannerCodes', [
            'foreignKey' => 'mark_form_property_id'
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
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('name', 'create')
            ->notEmpty('name')
            ->add('name', 'unique', [
                'rule' => 'validateUnique',
                'provider' => 'table',
                'message' => __('This name has already been used.'),
            ]);

        $validator
            ->requirePresence('field_type', 'create')
            ->notEmpty('field_type');

        $validator
            ->requirePresence('validation_rule', 'create')
            ->allowEmpty('validation_rule');

        $validator
            ->add('min', 'custom', [
                'rule' => function($value, $context) {
                    if (in_array($context['data']['field_type'], ['INTEGER', 'FLOAT'])) {
                        return $value < $context['data']['max'];
                    } else {
                        return true;
                    }
                },
                'message' => __('The min value is required and must be smaller than the max value'),
            ]);

        $validator
            ->add('max', 'custom', [
                'rule' => function($value, $context) {
                    if (in_array($context['data']['field_type'], ['INTEGER', 'FLOAT'])) {
                        return $value > $context['data']['min'];
                    } else {
                        return true;
                    }
                },
                'message' => __('The max value is required and must be greater than the max value'),
            ]);

        $validator
            ->add('step', 'custom', [
                'rule' => function($value, $context) {
                    if (in_array($context['data']['field_type'], ['INTEGER', 'FLOAT'])) {
                        return ($value > 0) && ($value <= ($context['data']['max'] - $context['data']['min']));
                    } else {
                        return true;
                    }
                },
                'message' => __('The step value is required and must be greater than zero and smaller or equal to the difference between the max and the min value.'),
            ]);

        $validator
            ->add('tree_property', 'custom', [
                'rule' => function($value, $context) {
                    if ($value) {
                        return true;
                    } else {
                        return $context['data']['variety_property'] || $context['data']['batch_property'];
                    }
                },
                'message' => __('Select at least one domain'),
            ]);

        $validator
            ->add('variety_property', 'custom', [
                'rule' => function($value, $context) {
                    if ($value) {
                        return true;
                    } else {
                        return $context['data']['tree_property'] || $context['data']['batch_property'];
                    }
                },
                'message' => __('Select at least one domain'),
            ]);

        $validator
            ->add('batch_property', 'custom', [
                'rule' => function($value, $context) {
                    if ($value) {
                        return true;
                    } else {
                        return $context['data']['variety_property'] || $context['data']['tree_property'];
                    }
                },
                'message' => __('Select at least one domain'),
            ]);

        $validator
            ->requirePresence('mark_form_property_type_id', 'create')
            ->notEmpty('mark_form_property_type_id');

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
        $rules->add($rules->existsIn(['mark_form_property_type_id'], 'MarkFormPropertyTypes'));

        $rules->addDelete(new IsNotReferredBy(['MarkFormFields' => 'mark_form_property_id']),'isNotReferredBy');
        $rules->addDelete(new IsNotReferredBy(['MarkValues' => 'mark_form_property_id']),'isNotReferredBy');
        $rules->addDelete(new IsNotReferredBy(['MarkScannerCodes' => 'mark_form_property_id']),'isNotReferredBy');

        return $rules;
    }

    public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
    {
        $data['validation_rule'] = $this->buildValidationRuleFieldData($data);
    }

    /**
     * Return a JSON with the validation rules
     *
     * @param  ArrayObject $data the request data
     * @return String JSON object with the validation rule
     */
    public function buildValidationRuleFieldData(ArrayObject $data) {
        $validation_rule = array();
                
        if ( in_array($data['field_type'], ['INTEGER', 'FLOAT']) ) {
            $validation_rule = [
                'min' => isset( $data['min'] ) ? $data['min'] : PHP_INT_MIN,
                'max' => isset( $data['max'] ) ? $data['max'] : PHP_INT_MAX,
                'step' => isset( $data['step'] ) ? $data['step'] : 1,
            ];
        }

        return $validation_rule;
    }

    /**
     * Return array with field type as key and description as value
     *
     * @return array
     */
    public function getFieldTypes()
    {
        return [
            'INTEGER' => __('Integer'),
            'FLOAT'   => __('Floating-point number'),
            'VARCHAR' => __('Text ( < 255 characters)'),
            'BOOLEAN' => __('Boolean'),
            'DATE'    => __('Date'),
        ];
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
                ->contain(['MarkFormPropertyTypes'])
                ->where(['MarkFormProperties.name LIKE' => '%'.$where.'%']);

        return $query;
    }
}
