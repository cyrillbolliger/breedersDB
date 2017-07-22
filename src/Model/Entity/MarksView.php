<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * MarksView Entity
 *
 * @property \Cake\I18n\Time $date
 * @property string $author
 * @property int $tree_id
 * @property int $variety_id
 * @property int $batch_id
 * @property string $value
 * @property bool $exceptional_mark
 * @property string $name
 * @property string $field_type
 * @property string $mark_form_property_type
 *
 * @property \App\Model\Entity\Tree $tree
 * @property \App\Model\Entity\Variety $variety
 * @property \App\Model\Entity\Batch $batch
 */
class MarksView extends Entity
{

}
