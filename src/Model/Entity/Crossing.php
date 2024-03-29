<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Crossing Entity
 *
 * @property int $id
 * @property string $code
 * @property int $mother_variety_id
 * @property int $father_variety_id
 * @property string $target
 * @property \Cake\I18n\FrozenTime $deleted
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Variety $mother_variety
 * @property \App\Model\Entity\Variety $father_variety
 * @property \App\Model\Entity\MotherTree[] $mother_trees
 * @property \App\Model\Entity\Batch[] $batches
 */
class Crossing extends Entity {

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

	protected function _getMotherConvar() {
		return $this->mother_variety->convar;
	}

	protected function _getFatherConvar() {
		return $this->father_variety->convar;
	}
}
