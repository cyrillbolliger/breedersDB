<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * MotherTree Entity
 *
 * @property int $id
 * @property string $code
 * @property bool $planed
 * @property \Cake\I18n\FrozenTime $date_pollen_harvested
 * @property \Cake\I18n\FrozenTime $date_impregnated
 * @property \Cake\I18n\FrozenTime $date_fruit_harvested
 * @property int $numb_portions
 * @property int $numb_flowers
 * @property int $numb_fruits
 * @property int $numb_seeds
 * @property string $note
 * @property int $tree_id
 * @property int $crossing_id
 * @property \Cake\I18n\FrozenTime $deleted
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Tree $tree
 * @property \App\Model\Entity\Crossing $crossing
 */
class MotherTree extends Entity {

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
