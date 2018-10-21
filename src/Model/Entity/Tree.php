<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

/**
 * Tree Entity
 *
 * @property int $id
 * @property string $publicid
 * @property \Cake\I18n\Time $date_grafted
 * @property \Cake\I18n\Time $date_planted
 * @property \Cake\I18n\Time $date_eliminated
 * @property bool $genuine_seedling
 * @property bool $migrated_tree
 * @property float $offset
 * @property string $dont_eliminate
 * @property string $note
 * @property bool $deleted
 * @property bool $locked
 * @property int $variety_id
 * @property int $rootstock_id
 * @property int $grafting_id
 * @property int $row_id
 * @property int $experiment_site_id
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Variety $variety
 * @property \App\Model\Entity\Rootstock $rootstock
 * @property \App\Model\Entity\Grafting $grafting
 * @property \App\Model\Entity\Row $row
 * @property \App\Model\Entity\ExperimentSite $experiment_site
 * @property \App\Model\Entity\Mark[] $marks
 */
class Tree extends Entity {
	
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
		$Varieties = TableRegistry::get( 'Varieties' );
		$variety   = $Varieties->get( $this->variety_id );
		$batch     = $Batches->get( $variety->batch_id );
		$crossing  = $Crossings->get( $batch->crossing_id );
		
		return $crossing->code . '.' . $batch->code . '.' . $variety->code;
	}
	
	protected function _getRowCode() {
		if ( ! $this->row_id ) {
			return null;
		}
		
		$Rows = TableRegistry::get( 'Rows' );
		$row  = $Rows->get( $this->row_id );
		
		return $row->code;
	}
}
