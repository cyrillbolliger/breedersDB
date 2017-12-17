<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * MarkScannerCode Entity
 *
 * @property int $id
 * @property string $code
 * @property string $mark_value
 * @property int $mark_form_property_id
 *
 * @property \App\Model\Entity\MarkFormProperty $mark_form_property
 */
class MarkScannerCode extends Entity {
	
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
