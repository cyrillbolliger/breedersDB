<?php

namespace App\Generator;

/**
 * MarkFormFields generator.
 */
class MarkFormFieldsGenerator {

    public function generate( int $count = 50 ) {
        $formsTable = \Cake\ORM\TableRegistry::getTableLocator()->get( 'MarkForms' );
        $markForms  = $formsTable->find();

        $propertiesTable = \Cake\ORM\TableRegistry::getTableLocator()->get( 'MarkFormProperties' );
        $properties      = $propertiesTable->find();

        $perForm = ceil( $markForms->count() / $count );

        $data = [];
        foreach ( $markForms as $form ) {
            for ( $i = 0; $i < $perForm; $i ++ ) {
                $data = [
                    [
                        'priority'              => $i,
                        'mark_form_id'          => $form->id,
                        'mark_form_property_id' => $properties->toArray()[$i]->id,
                        'created'               => date('Y-m-d H:i:s'),
                        'modified'              => date('Y-m-d H:i:s'),
                    ]
                ];
            }
        }

        return $data;
    }
}
