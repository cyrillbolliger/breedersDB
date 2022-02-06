<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * TreesView Entity
 *
 * @property int $id
 * @property string $publicid
 * @property string $convar
 * @property \Cake\I18n\FrozenTime $date_grafted
 * @property \Cake\I18n\FrozenTime $date_planted
 * @property \Cake\I18n\FrozenTime $date_eliminated
 * @property bool $genuine_seedling
 * @property float $offset
 * @property string $row
 * @property string $dont_eliminate
 * @property string $note
 * @property int $variety_id
 * @property string $grafting
 * @property string $rootstock
 * @property string $experiment_site
 *
 * @property \App\Model\Entity\Variety $variety
 */
class TreesView extends Entity {

}
