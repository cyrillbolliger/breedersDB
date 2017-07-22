<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ScionsBundlesView Entity
 *
 * @property int $id
 * @property string $identification
 * @property string $convar
 * @property int $numb_scions
 * @property \Cake\I18n\Time $date_scions_harvest
 * @property string $descents_publicid_list
 * @property string $note
 * @property bool $external_use
 * @property int $variety_id
 *
 * @property \App\Model\Entity\Variety $variety
 */
class ScionsBundlesView extends Entity
{

}
