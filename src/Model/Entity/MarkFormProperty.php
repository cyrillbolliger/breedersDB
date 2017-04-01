<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * MarkFormProperty Entity
 *
 * @property int $id
 * @property string $name
 * @property string $validation_rule
 * @property string $field_type
 * @property int $mark_form_property_type_id
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\MarkFormPropertyType $mark_form_property_type
 * @property \App\Model\Entity\MarkFormField[] $mark_form_fields
 * @property \App\Model\Entity\MarkValue[] $mark_values
 */
class MarkFormProperty extends Entity
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
        '*' => true,
        'id' => false
    ];
}
