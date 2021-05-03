<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Row Entity
 *
 * @property int $id
 * @property string $code
 * @property string $note
 * @property \Cake\I18n\Time $date_created
 * @property \Cake\I18n\Time $date_eliminated
 * @property \Cake\I18n\Time $deleted
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Tree[] $trees
 */
class Row extends Entity {

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
