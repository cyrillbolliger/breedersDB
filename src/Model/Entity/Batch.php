<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Batch Entity
 *
 * @property int $id
 * @property string $code
 * @property \Cake\I18n\Time $date_sowed
 * @property int $numb_seeds_sowed
 * @property int $numb_sprouts_grown
 * @property string $seed_tray
 * @property \Cake\I18n\Time $date_planted
 * @property int $numb_sprouts_planted
 * @property string $patch
 * @property string $note
 * @property bool $deleted
 * @property bool $locked
 * @property int $crossing_id
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Crossing $crossing
 * @property \App\Model\Entity\Mark[] $marks
 * @property \App\Model\Entity\Variety[] $varieties
 */
class Batch extends Entity
{
    
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
    
    /**
     * Get virtual property
     *
     * @return string
     */
    protected function _getCrossingBatch()
    {
        return $this->crossing->code . '.' . $this->code;
    }
}
