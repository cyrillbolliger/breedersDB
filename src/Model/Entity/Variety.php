<?php

namespace App\Model\Entity;

use Cake\Core\Configure;
use Cake\ORM\Entity;

/**
 * Variety Entity
 *
 * @property int $id
 * @property string $code
 * @property string $official_name
 * @property string $acronym
 * @property string $variety
 * @property string $plant_breeder
 * @property string $registration
 * @property string $description
 * @property bool $deleted
 * @property bool $locked
 * @property int $batch_id
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property string $convar
 *
 * @property \App\Model\Entity\Batch $batch
 * @property \App\Model\Entity\ScionsBundle[] $scions_bundles
 * @property \App\Model\Entity\Tree[] $trees
 */
class Variety extends Entity {

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

	protected $_virtual = [ 'breeder_variety_code' ];

	protected function _getBreederVarietyCode() {
		return Configure::readOrFail('Org.abbreviation') . sprintf( '%0' . BREEDER_VARIETY_CODE_NUM_LENGTH . 'd', $this->id );
	}
}
