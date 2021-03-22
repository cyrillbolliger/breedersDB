<?php
namespace App\Generator;


/**
 * MarkFormProperties generator.
 */
class MarkFormPropertiesGenerator {

    public function generate() {
        $data = [
            [
                'name'                       => 'Schorf Blatt',
                'validation_rule'            => '{"min":"1","max":"9","step":"1"}',
                'field_type'                 => 'INTEGER',
                'note'                       => null,
                'mark_form_property_type_id' => '1',
                'created'                    => date( 'Y-m-d H:m:s' ),
                'modified'                   => date( 'Y-m-d H:m:s' ),
                'tree_property'              => '1',
                'variety_property'           => '1',
                'batch_property'             => '1',
            ],
            [
                'name'                       => 'Schorf Frucht',
                'validation_rule'            => '{"min":"1","max":"9","step":"1"}',
                'field_type'                 => 'INTEGER',
                'note'                       => null,
                'mark_form_property_type_id' => '1',
                'created'                    => date( 'Y-m-d H:m:s' ),
                'modified'                   => date( 'Y-m-d H:m:s' ),
                'tree_property'              => '1',
                'variety_property'           => '1',
                'batch_property'             => '0',
            ],
            [
                'name'                       => 'Mehltau Blatt',
                'validation_rule'            => '{"min":"1","max":"9","step":"1"}',
                'field_type'                 => 'INTEGER',
                'note'                       => null,
                'mark_form_property_type_id' => '1',
                'created'                    => date( 'Y-m-d H:m:s' ),
                'modified'                   => date( 'Y-m-d H:m:s' ),
                'tree_property'              => '1',
                'variety_property'           => '1',
                'batch_property'             => '1',
            ],
            [
                'name'                       => 'Marssonina',
                'validation_rule'            => '{"min":"1","max":"9","step":"1"}',
                'field_type'                 => 'INTEGER',
                'note'                       => null,
                'mark_form_property_type_id' => '1',
                'created'                    => date( 'Y-m-d H:m:s' ),
                'modified'                   => date( 'Y-m-d H:m:s' ),
                'tree_property'              => '1',
                'variety_property'           => '1',
                'batch_property'             => '0',
            ],
            [
                'name'                       => 'Krebs',
                'validation_rule'            => '[]',
                'field_type'                 => 'BOOLEAN',
                'note'                       => 'nicht mehr verwenden',
                'mark_form_property_type_id' => '1',
                'created'                    => date( 'Y-m-d H:m:s' ),
                'modified'                   => date( 'Y-m-d H:m:s' ),
                'tree_property'              => '1',
                'variety_property'           => '1',
                'batch_property'             => '0',
            ],
            [
                'name'                       => 'Behang',
                'validation_rule'            => '{"min":"1","max":"9","step":"1"}',
                'field_type'                 => 'INTEGER',
                'note'                       => '',
                'mark_form_property_type_id' => '1',
                'created'                    => date( 'Y-m-d H:m:s' ),
                'modified'                   => date( 'Y-m-d H:m:s' ),
                'tree_property'              => '1',
                'variety_property'           => '0',
                'batch_property'             => '0',
            ],
            [
                'name'                       => 'Ausgedünnt',
                'validation_rule'            => '[]',
                'field_type'                 => 'BOOLEAN',
                'note'                       => null,
                'mark_form_property_type_id' => '2',
                'created'                    => date( 'Y-m-d H:m:s' ),
                'modified'                   => date( 'Y-m-d H:m:s' ),
                'tree_property'              => '1',
                'variety_property'           => '0',
                'batch_property'             => '0',
            ],
            [
                'name'                       => 'Apfellaus mehlige',
                'validation_rule'            => '{"min":"1","max":"9","step":"1"}',
                'field_type'                 => 'INTEGER',
                'note'                       => null,
                'mark_form_property_type_id' => '1',
                'created'                    => date( 'Y-m-d H:m:s' ),
                'modified'                   => date( 'Y-m-d H:m:s' ),
                'tree_property'              => '1',
                'variety_property'           => '1',
                'batch_property'             => '1',
            ],
            [
                'name'                       => 'Phyllosticca',
                'validation_rule'            => '{"min":"1","max":"9","step":"1"}',
                'field_type'                 => 'INTEGER',
                'note'                       => null,
                'mark_form_property_type_id' => '1',
                'created'                    => date( 'Y-m-d H:m:s' ),
                'modified'                   => date( 'Y-m-d H:m:s' ),
                'tree_property'              => '1',
                'variety_property'           => '1',
                'batch_property'             => '0',
            ],
            [
                'name'                       => 'Faltenlaus',
                'validation_rule'            => '{"min":"1","max":"9","step":"1"}',
                'field_type'                 => 'INTEGER',
                'note'                       => null,
                'mark_form_property_type_id' => '1',
                'created'                    => date( 'Y-m-d H:m:s' ),
                'modified'                   => date( 'Y-m-d H:m:s' ),
                'tree_property'              => '1',
                'variety_property'           => '1',
                'batch_property'             => '1',
            ],
            [
                'name'                       => 'Rote Spinne',
                'validation_rule'            => '{"min":"1","max":"9","step":"1"}',
                'field_type'                 => 'INTEGER',
                'note'                       => null,
                'mark_form_property_type_id' => '1',
                'created'                    => date( 'Y-m-d H:m:s' ),
                'modified'                   => date( 'Y-m-d H:m:s' ),
                'tree_property'              => '1',
                'variety_property'           => '1',
                'batch_property'             => '1',
            ],
            [
                'name'                       => 'Erntedatum',
                'validation_rule'            => '[]',
                'field_type'                 => 'DATE',
                'note'                       => null,
                'mark_form_property_type_id' => '1',
                'created'                    => date( 'Y-m-d H:m:s' ),
                'modified'                   => date( 'Y-m-d H:m:s' ),
                'tree_property'              => '1',
                'variety_property'           => '1',
                'batch_property'             => '0',
            ],
            [
                'name'                       => 'Anz. Früchte geerntet',
                'validation_rule'            => '{"min":"0","max":"999","step":"1"}',
                'field_type'                 => 'INTEGER',
                'note'                       => '',
                'mark_form_property_type_id' => '3',
                'created'                    => date( 'Y-m-d H:m:s' ),
                'modified'                   => date( 'Y-m-d H:m:s' ),
                'tree_property'              => '1',
                'variety_property'           => '0',
                'batch_property'             => '0',
            ],
            [
                'name'                       => 'Aussehen Form/Farbe',
                'validation_rule'            => '{"min":"1","max":"9","step":"1"}',
                'field_type'                 => 'INTEGER',
                'note'                       => null,
                'mark_form_property_type_id' => '1',
                'created'                    => date( 'Y-m-d H:m:s' ),
                'modified'                   => date( 'Y-m-d H:m:s' ),
                'tree_property'              => '1',
                'variety_property'           => '1',
                'batch_property'             => '0',
            ],
            [
                'name'                       => 'Regenflecken',
                'validation_rule'            => '{"min":"1","max":"9","step":"1"}',
                'field_type'                 => 'INTEGER',
                'note'                       => null,
                'mark_form_property_type_id' => '1',
                'created'                    => date( 'Y-m-d H:m:s' ),
                'modified'                   => date( 'Y-m-d H:m:s' ),
                'tree_property'              => '1',
                'variety_property'           => '1',
                'batch_property'             => '0',
            ],
            [
                'name'                       => 'Fleischverbräunung',
                'validation_rule'            => '[]',
                'field_type'                 => 'BOOLEAN',
                'note'                       => null,
                'mark_form_property_type_id' => '1',
                'created'                    => date( 'Y-m-d H:m:s' ),
                'modified'                   => date( 'Y-m-d H:m:s' ),
                'tree_property'              => '1',
                'variety_property'           => '1',
                'batch_property'             => '0',
            ],
            [
                'name'                       => 'Biss',
                'validation_rule'            => '{"min":"1","max":"9","step":"1"}',
                'field_type'                 => 'INTEGER',
                'note'                       => null,
                'mark_form_property_type_id' => '1',
                'created'                    => date( 'Y-m-d H:m:s' ),
                'modified'                   => date( 'Y-m-d H:m:s' ),
                'tree_property'              => '1',
                'variety_property'           => '1',
                'batch_property'             => '0',
            ],
            [
                'name'                       => 'Saftigkeit',
                'validation_rule'            => '{"min":"1","max":"9","step":"1"}',
                'field_type'                 => 'INTEGER',
                'note'                       => null,
                'mark_form_property_type_id' => '1',
                'created'                    => date( 'Y-m-d H:m:s' ),
                'modified'                   => date( 'Y-m-d H:m:s' ),
                'tree_property'              => '1',
                'variety_property'           => '1',
                'batch_property'             => '0',
            ],
            [
                'name'                       => 'Aromatik',
                'validation_rule'            => '{"min":"1","max":"9","step":"1"}',
                'field_type'                 => 'INTEGER',
                'note'                       => null,
                'mark_form_property_type_id' => '1',
                'created'                    => date( 'Y-m-d H:m:s' ),
                'modified'                   => date( 'Y-m-d H:m:s' ),
                'tree_property'              => '1',
                'variety_property'           => '1',
                'batch_property'             => '0',
            ],
            [
                'name'                       => 'Brix',
                'validation_rule'            => '{"min":"5","max":"19","step":"0.1"}',
                'field_type'                 => 'FLOAT',
                'note'                       => null,
                'mark_form_property_type_id' => '1',
                'created'                    => date( 'Y-m-d H:m:s' ),
                'modified'                   => date( 'Y-m-d H:m:s' ),
                'tree_property'              => '1',
                'variety_property'           => '1',
                'batch_property'             => '0',
            ],
            [
                'name'                       => 'Bemerkung',
                'validation_rule'            => '[]',
                'field_type'                 => 'VARCHAR',
                'note'                       => null,
                'mark_form_property_type_id' => '3',
                'created'                    => date( 'Y-m-d H:m:s' ),
                'modified'                   => date( 'Y-m-d H:m:s' ),
                'tree_property'              => '1',
                'variety_property'           => '1',
                'batch_property'             => '1',
            ],
            [
                'name'                       => 'Anz. Früchte am Baum',
                'validation_rule'            => '{"min":"0","max":"999","step":"1"}',
                'field_type'                 => 'INTEGER',
                'note'                       => '',
                'mark_form_property_type_id' => '1',
                'created'                    => date( 'Y-m-d H:m:s' ),
                'modified'                   => date( 'Y-m-d H:m:s' ),
                'tree_property'              => '1',
                'variety_property'           => '0',
                'batch_property'             => '0',
            ],
        ];

        return $data;
    }
}