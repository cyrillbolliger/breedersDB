<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\I18n\Date;

/**
 * MarkValue Entity
 *
 * @property int $id
 * @property string $value
 * @property bool $exceptional_mark
 * @property int $mark_form_property_id
 * @property int $mark_id
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\MarkFormProperty $mark_form_property
 * @property \App\Model\Entity\Mark $mark
 */
class MarkValue extends Entity
{
    
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*'  => true,
        'id' => false
    ];
    
    /**
     * Return nicely formatted date if its a date. Else just return the value as it is.
     * The conversion to the ymd format at saving is done in the beforeMarshalling method of the table.
     *
     * @return string
     */
    protected function _getValue()
    {
        $MarkFormProperties = TableRegistry::get('MarkFormProperties');
        
        $type = $MarkFormProperties->get($this->_properties['mark_form_property_id'])->field_type;
        
        if ('DATE' === $type) {
            $date = Date::parse($this->_properties['value']);
            return $date->i18nFormat();
        }
        
        return $this->_properties['value'];
    }
}
