<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Query Entity
 *
 * @property int $id
 * @property string $code
 * @property string $query
 * @property string $description
 * @property int $query_group_id
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\QueryGroup $query_group
 */
class Query extends Entity
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
}
