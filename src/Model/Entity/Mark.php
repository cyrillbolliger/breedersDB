<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Mark Entity
 *
 * @property int $id
 * @property \Cake\I18n\Time $date
 * @property string $author
 * @property string $locked
 * @property int $mark_form_id
 * @property int $tree_id
 * @property int $clone_id
 * @property int $batch_id
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\MarkForm $mark_form
 * @property \App\Model\Entity\Tree $tree
 * @property \App\Model\Entity\Variety $variety
 * @property \App\Model\Entity\Batch $batch
 * @property \App\Model\Entity\MarkValue[] $mark_values
 */
class Mark extends Entity {
	
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
