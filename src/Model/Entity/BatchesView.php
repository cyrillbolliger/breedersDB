<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * BatchesView Entity
 *
 * @property int $id
 * @property string $crossing_batch
 * @property \Cake\I18n\Time $date_sowed
 * @property int $numb_seeds_sowed
 * @property int $numb_sprouts_grown
 * @property string $seed_tray
 * @property \Cake\I18n\Time $date_planted
 * @property int $numb_sprouts_planted
 * @property string $patch
 * @property string $note
 * @property int $crossing_id
 *
 * @property \App\Model\Entity\Crossing $crossing
 */
class BatchesView extends Entity
{

}
