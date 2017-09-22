<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\I18n\Date;

/**
 * MarksView Entity
 *
 * @property \Cake\I18n\Time $date
 * @property string $author
 * @property int $tree_id
 * @property int $variety_id
 * @property int $batch_id
 * @property string $value
 * @property bool $exceptional_mark
 * @property string $name
 * @property string $field_type
 * @property string $mark_form_property_type
 *
 * @property \App\Model\Entity\Tree $tree
 * @property \App\Model\Entity\Variety $variety
 * @property \App\Model\Entity\Batch $batch
 */
class MarksView extends Entity
{
    /**
     * Return nicely formatted date if its a date. Else just return the value as it is.
     * The conversion to the ymd format at saving is done in the beforeMarshalling method of the table.
     *
     * @return string
     */
    protected function _getValue()
    {
        $type = $this->_properties['field_type'];
        
        if ('DATE' === $type) {
            $date = Date::parse($this->_properties['value']);
            return $date->i18nFormat();
        }
        
        return $this->_properties['value'];
    }
}
