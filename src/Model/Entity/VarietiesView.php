<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * VarietiesView Entity
 *
 * @property int $id
 * @property string $convar
 * @property string $official_name
 * @property string $acronym
 * @property string $plant_breeder
 * @property string $registration
 * @property string $description
 * @property int $batch_id
 *
 * @property \App\Model\Entity\Batch $batch
 */
class VarietiesView extends Entity {

}
