<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

/**
 * MotherTree Entity
 *
 * @property int $id
 * @property string $code
 * @property bool $planed
 * @property \Cake\I18n\Time $date_pollen_harvested
 * @property \Cake\I18n\Time $date_impregnated
 * @property \Cake\I18n\Time $date_fruit_harvested
 * @property int $numb_portions
 * @property int $numb_flowers
 * @property int $numb_seeds
 * @property string $target
 * @property string $note
 * @property int $tree_id
 * @property int $crossing_id
 * @property \Cake\I18n\Time $deleted
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Tree $tree
 * @property \App\Model\Entity\Crossing[] $crossings
 */
class MotherTree extends Entity
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
