<?php

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Users Model
 *
 * @method \App\Model\Entity\User get($primaryKey, $options = [])
 * @method \App\Model\Entity\User newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\User[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\User|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\User[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\User findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UsersTable extends Table
{
    
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
        
        $this->table('users');
        $this->displayField('id');
        $this->primaryKey('id');
        
        $this->addBehavior('Timestamp');
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
            ->allowEmpty('id', 'create');
        
        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmpty('email');
        
        $validator
            ->requirePresence('password', 'create')
            ->notEmpty('password');
    
        $validator
            ->requirePresence('time_zone', 'create')
            ->notEmpty('time_zone');
        
        $validator
            ->integer('level')
            ->requirePresence('level', 'create')
            ->notEmpty('level');
        
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
        $rules->add($rules->isUnique(['email']));
        
        return $rules;
    }
    
    /**
     * Return a selection of timezones as arry with a timeszone string as key and a description as value
     *
     * @return array
     */
    public function getTimezones() {
        return [
            "Pacific/Kwajalein" => "(GMT -12:00) Eniwetok, Kwajalein",
            "Pacific/Samoa" => "(GMT -11:00) Midway Island, Samoa",
            "Pacific/Honolulu" => "(GMT -10:00) Hawaii",
            "America/Anchorage" => "(GMT -9:00) Alaska",
            "America/Los_Angeles" => "(GMT -8:00) Pacific Time (US &amp; Canada)",
            "America/Denver" => "(GMT -7:00) Mountain Time (US &amp; Canada)",
            "America/Chicago" => "(GMT -6:00) Central Time (US &amp; Canada), Mexico City",
            "America/New_York" => "(GMT -5:00) Eastern Time (US &amp; Canada), Bogota, Lima",
            "Atlantic/Bermuda" => "(GMT -4:00) Atlantic Time (Canada), Caracas, La Paz",
            "Canada/Newfoundland" => "(GMT -3:30) Newfoundland",
            "Brazil/East" => "(GMT -3:00) Brazil, Buenos Aires, Georgetown",
            "Atlantic/Azores" => "(GMT -2:00) Mid-Atlantic",
            "Atlantic/Cape_Verde" => "(GMT -1:00 hour) Azores, Cape Verde Islands",
            "Europe/London" => "(GMT) Western Europe Time, London, Lisbon, Casablanca",
            "Europe/Brussels" => "(GMT +1:00 hour) Brussels, Copenhagen, Madrid, Paris, Berlin, Zurich",
            "Europe/Helsinki" => "(GMT +2:00) Kaliningrad, South Africa",
            "Asia/Baghdad" => "(GMT +3:00) Baghdad, Riyadh, Moscow, St. Petersburg",
            "Asia/Tehran" => "(GMT +3:30) Tehran",
            "Asia/Baku" => "(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi",
            "Asia/Kabul" => "(GMT +4:30) Kabul",
            "Asia/Karachi" => "(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent",
            "Asia/Calcutta" => "(GMT +5:30) Bombay, Calcutta, Madras, New Delhi",
            "Asia/Dhaka" => "(GMT +6:00) Almaty, Dhaka, Colombo",
            "Asia/Bangkok" => "(GMT +7:00) Bangkok, Hanoi, Jakarta",
            "Asia/Hong_Kong" => "(GMT +8:00) Beijing, Perth, Singapore, Hong Kong",
            "Asia/Tokyo" => "(GMT +9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk",
            "Australia/Adelaide" => "(GMT +9:30) Adelaide, Darwin",
            "Pacific/Guam" => "(GMT +10:00) Eastern Australia, Guam, Vladivostok",
            "Asia/Magadan" => "(GMT +11:00) Magadan, Solomon Islands, New Caledonia",
            "Pacific/Fiji" => "(GMT +12:00) Auckland, Wellington, Fiji, Kamchatka"
        ];
    }
}
