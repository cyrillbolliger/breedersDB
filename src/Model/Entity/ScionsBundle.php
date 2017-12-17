<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

/**
 * ScionsBundle Entity
 *
 * @property int $id
 * @property string $code
 * @property int $numb_scions
 * @property \Cake\I18n\Time $date_scions_harvest
 * @property string $descents_publicid_list
 * @property string $note
 * @property bool $external_use
 * @property bool $deleted
 * @property bool $locked
 * @property int $variety_id
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Variety $variety
 */
class ScionsBundle extends Entity {
	
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
	
	protected function _getConvar() {
		$Crossings = TableRegistry::get( 'Crossings' );
		$Batches   = TableRegistry::get( 'Batches' );
		$variety   = $this->variety;
		$batch     = $Batches->get( $variety->batch_id );
		$crossing  = $Crossings->get( $batch->crossing_id );
		
		return $crossing->code . '.' . $batch->code . '.' . $variety->code;
	}
}
