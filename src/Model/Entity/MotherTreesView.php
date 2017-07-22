<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * MotherTreesView Entity
 *
 * @property int $id
 * @property string $code
 * @property bool $planed
 * @property \Cake\I18n\Time $date_pollen_harvested
 * @property \Cake\I18n\Time $date_impregnated
 * @property \Cake\I18n\Time $date_fruit_harvested
 * @property int $numb_portions
 * @property int $numb_flowers
 * @property int $numb_seeds
 * @property string $target
 * @property string $note
 * @property string $publicid
 * @property float $offset
 * @property string $row
 * @property string $experiment_site
 * @property int $tree_id
 * @property int $crossing_id
 *
 * @property \App\Model\Entity\Crossing $crossing
 * @property \App\Model\Entity\Tree $tree
 */
class MotherTreesView extends Entity
{

}
