<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * MarkFormField Entity
 *
 * @property int $id
 * @property int $priority
 * @property int $mark_form_id
 * @property int $mark_form_property_id
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\MarkForm $mark_form
 * @property \App\Model\Entity\MarkFormProperty $mark_form_property
 */
class MarkFormField extends Entity {

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
