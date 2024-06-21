<?php

namespace App\View\Helper;

use App\Model\Entity\MarkValue;
use Cake\View\Helper;

class MarkPhotoPropsHelper extends Helper {
    public $helpers = [
        'Url'
    ];

    public function getProps(MarkValue $markValue) {
        $url = $this->Url->build(['prefix' => 'REST1', 'controller' => 'Photos', 'action' => 'view', $markValue->value]);
        $fileExt = pathinfo($url, PATHINFO_EXTENSION);
        $entityName = $markValue->mark->tree?->name ?? $markValue->mark->tree?->publicid ?? $markValue->mark->variety?->convar ?? $markValue->mark->batch?->code;
        if (empty($entityName)) {
            throw new \InvalidArgumentException('$markValue is missing a tree, variety, or batch.');
        }
        return [
            'url' => $url,
            'title' => $entityName . ', ' . $markValue->mark_form_property->name . ', ' . $markValue->mark->date . ', ' . $markValue->mark->author,
            'downloadName' => \Cake\Core\Configure::read('Org.abbreviation')
                . '-' . $entityName
                . '-' . $markValue->mark->created->format('Ymd\THis')
                . '-' . $markValue->mark->id
                . '.' . $fileExt,
        ];
    }
}
