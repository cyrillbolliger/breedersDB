<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

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
}
