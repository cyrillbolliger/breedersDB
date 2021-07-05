<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Tree Entity
 *
 * @property int $id
 * @property string $publicid
 * @property \Cake\I18n\Time $date_grafted
 * @property \Cake\I18n\Time $date_planted
 * @property \Cake\I18n\Time $date_eliminated
 * @property \Cake\I18n\Time $date_labeled
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
 * @property string $convar
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

	protected function _getRowCode() {
		if ( ! $this->row_id ) {
			return null;
		}

		$Rows = \Cake\Datasource\FactoryLocator::get('Table')->get( 'Rows' );
		$row  = $Rows->get( $this->row_id );

		return $row->code;
	}
}
