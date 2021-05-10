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
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\MarkForm $MarkForm
 * @property \App\Model\Entity\MarkFormProperty $MarkFormProperty
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
