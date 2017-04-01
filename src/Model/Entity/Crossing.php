<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

/**
 * Crossing Entity
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
 * @property bool $deleted
 * @property bool $locked
 * @property int $mother_variety_id
 * @property int $father_variety_id
 * @property int $mother_tree_id
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Variety $variety
 * @property \App\Model\Entity\Tree $tree
 * @property \App\Model\Entity\Batch[] $batches
 */
class Crossing extends Entity
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
        '*' => true,
        'id' => false
    ];
    
    protected function _getMotherConvar() {
        $Varieties = TableRegistry::get('Varieties');
        $Batches = TableRegistry::get('Batches');
        $variety = $Varieties->get($this->mother_variety_id);
        $batch = $Batches->get($variety->batch_id);
        return $this->code .'.'. $batch->code .'.'. $variety->code;
    }
    
    protected function _getFatherConvar() {
        $Varieties = TableRegistry::get('Varieties');
        $Batches = TableRegistry::get('Batches');
        $variety = $Varieties->get($this->father_variety_id);
        $batch = $Batches->get($variety->batch_id);
        return $this->code .'.'. $batch->code .'.'. $variety->code;
    }
}
