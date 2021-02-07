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
 * @property \Cake\I18n\Time $deleted
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Variety $variety
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
	    $tableLocator = \Cake\Datasource\FactoryLocator::get('Table');

		$Varieties = $tableLocator->get( 'Varieties' );
		$Batches   = $tableLocator->get( 'Batches' );
		$variety   = $Varieties->get( $this->mother_variety_id );
		$batch     = $Batches->get( $variety->batch_id );

		return $this->code . '.' . $batch->code . '.' . $variety->code;
	}

	protected function _getFatherConvar() {
        $tableLocator = \Cake\Datasource\FactoryLocator::get('Table');

        $Varieties = $tableLocator->get( 'Varieties' );
		$Batches   = $tableLocator->get( 'Batches' );
		$variety   = $Varieties->get( $this->father_variety_id );
		$batch     = $Batches->get( $variety->batch_id );

		return $this->code . '.' . $batch->code . '.' . $variety->code;
	}
}
