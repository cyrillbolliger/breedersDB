<?php

use Cake\Auth\DefaultPasswordHasher;
use Migrations\AbstractMigration;

class Initial extends AbstractMigration {
    public function up() {

        $this->table( 'batches' )
             ->addColumn( 'code', 'string', [
                 'default' => null,
                 'limit'   => 3,
                 'null'    => false,
             ] )
             ->addColumn( 'date_sowed', 'date', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'numb_seeds_sowed', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => true,
             ] )
             ->addColumn( 'numb_sprouts_grown', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => true,
             ] )
             ->addColumn( 'seed_tray', 'string', [
                 'default' => null,
                 'limit'   => 140,
                 'null'    => true,
             ] )
             ->addColumn( 'date_planted', 'date', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'numb_sprouts_planted', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => true,
             ] )
             ->addColumn( 'patch', 'string', [
                 'default' => null,
                 'limit'   => 140,
                 'null'    => true,
             ] )
             ->addColumn( 'note', 'text', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'crossing_id', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => false,
             ] )
             ->addColumn( 'deleted', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'created', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'modified', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addIndex(
                 [
                     'crossing_id',
                 ]
             )
             ->addIndex(
                 [
                     'code',
                 ]
             )
             ->create();

        $this->table( 'crossings' )
             ->addColumn( 'code', 'string', [
                 'default' => null,
                 'limit'   => 8,
                 'null'    => false,
             ] )
             ->addColumn( 'mother_variety_id', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => true,
             ] )
             ->addColumn( 'father_variety_id', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => true,
             ] )
             ->addColumn( 'target', 'text', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'deleted', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'created', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'modified', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addIndex(
                 [
                     'father_variety_id',
                 ]
             )
             ->addIndex(
                 [
                     'mother_variety_id',
                 ]
             )
             ->addIndex(
                 [
                     'code',
                 ]
             )
             ->create();

        $this->table( 'experiment_sites' )
             ->addColumn( 'name', 'string', [
                 'default' => null,
                 'limit'   => 140,
                 'null'    => false,
             ] )
             ->create();

        $this->table( 'graftings' )
             ->addColumn( 'name', 'string', [
                 'default' => null,
                 'limit'   => 140,
                 'null'    => false,
             ] )
             ->create();

        $this->table( 'mark_form_fields' )
             ->addColumn( 'priority', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => false,
             ] )
             ->addColumn( 'mark_form_id', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => false,
             ] )
             ->addColumn( 'mark_form_property_id', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => false,
             ] )
             ->addColumn( 'created', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'modified', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addIndex(
                 [
                     'mark_form_id',
                 ]
             )
             ->addIndex(
                 [
                     'mark_form_property_id',
                 ]
             )
             ->create();

        $this->table( 'mark_form_properties' )
             ->addColumn( 'name', 'string', [
                 'default' => null,
                 'limit'   => 45,
                 'null'    => false,
             ] )
             ->addColumn( 'validation_rule', 'string', [
                 'default' => null,
                 'limit'   => 255,
                 'null'    => false,
             ] )
             ->addColumn( 'field_type', 'string', [
                 'default' => null,
                 'limit'   => 45,
                 'null'    => false,
             ] )
             ->addColumn( 'note', 'text', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'mark_form_property_type_id', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => false,
             ] )
             ->addColumn( 'created', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'modified', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'tree_property', 'boolean', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => false,
             ] )
             ->addColumn( 'variety_property', 'boolean', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => false,
             ] )
             ->addColumn( 'batch_property', 'boolean', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => false,
             ] )
             ->addIndex(
                 [
                     'mark_form_property_type_id',
                 ]
             )
             ->addIndex(
                 [
                     'name',
                 ]
             )
             ->create();

        $this->table( 'mark_form_property_types' )
             ->addColumn( 'name', 'string', [
                 'default' => null,
                 'limit'   => 45,
                 'null'    => false,
             ] )
             ->addIndex(
                 [
                     'name',
                 ]
             )
             ->create();

        $this->table( 'mark_forms' )
             ->addColumn( 'name', 'string', [
                 'default' => null,
                 'limit'   => 45,
                 'null'    => false,
             ] )
             ->addColumn( 'description', 'text', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'created', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'modified', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addIndex(
                 [
                     'name',
                 ]
             )
             ->create();

        $this->table( 'mark_scanner_codes' )
             ->addColumn( 'code', 'string', [
                 'default' => null,
                 'limit'   => 45,
                 'null'    => false,
             ] )
             ->addColumn( 'mark_value', 'string', [
                 'default' => null,
                 'limit'   => 255,
                 'null'    => false,
             ] )
             ->addColumn( 'mark_form_property_id', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => false,
             ] )
             ->addIndex(
                 [
                     'mark_form_property_id',
                 ]
             )
             ->create();

        $this->table( 'mark_values' )
             ->addColumn( 'value', 'string', [
                 'default' => null,
                 'limit'   => 255,
                 'null'    => false,
             ] )
             ->addColumn( 'exceptional_mark', 'boolean', [
                 'default' => false,
                 'limit'   => null,
                 'null'    => false,
             ] )
             ->addColumn( 'mark_form_property_id', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => false,
             ] )
             ->addColumn( 'mark_id', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => false,
             ] )
             ->addColumn( 'created', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'modified', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addIndex(
                 [
                     'mark_form_property_id',
                 ]
             )
             ->addIndex(
                 [
                     'mark_id',
                 ]
             )
             ->create();

        $this->table( 'marks' )
             ->addColumn( 'date', 'date', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'author', 'string', [
                 'default' => null,
                 'limit'   => 45,
                 'null'    => true,
             ] )
             ->addColumn( 'mark_form_id', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => false,
             ] )
             ->addColumn( 'tree_id', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => true,
             ] )
             ->addColumn( 'variety_id', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => true,
             ] )
             ->addColumn( 'batch_id', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => true,
             ] )
             ->addColumn( 'created', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'modified', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addIndex(
                 [
                     'batch_id',
                 ]
             )
             ->addIndex(
                 [
                     'mark_form_id',
                 ]
             )
             ->addIndex(
                 [
                     'tree_id',
                 ]
             )
             ->addIndex(
                 [
                     'variety_id',
                 ]
             )
             ->create();

        $this->table( 'mother_trees' )
             ->addColumn( 'code', 'string', [
                 'default' => null,
                 'limit'   => 45,
                 'null'    => false,
             ] )
             ->addColumn( 'planed', 'boolean', [
                 'default' => false,
                 'limit'   => null,
                 'null'    => false,
             ] )
             ->addColumn( 'date_pollen_harvested', 'date', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'date_impregnated', 'date', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'date_fruit_harvested', 'date', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'numb_portions', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => true,
             ] )
             ->addColumn( 'numb_flowers', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => true,
             ] )
             ->addColumn( 'numb_fruits', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => true,
             ] )
             ->addColumn( 'numb_seeds', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => true,
             ] )
             ->addColumn( 'note', 'text', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'tree_id', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => true,
             ] )
             ->addColumn( 'crossing_id', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => false,
             ] )
             ->addColumn( 'deleted', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'created', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'modified', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addIndex(
                 [
                     'crossing_id',
                 ]
             )
             ->addIndex(
                 [
                     'tree_id',
                 ]
             )
             ->addIndex(
                 [
                     'code',
                 ]
             )
             ->create();

        $this->table( 'queries' )
             ->addColumn( 'code', 'string', [
                 'default' => null,
                 'limit'   => 120,
                 'null'    => false,
             ] )
             ->addColumn( 'my_query', 'text', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'description', 'text', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'query_group_id', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => false,
             ] )
             ->addColumn( 'deleted', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'created', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'modified', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addIndex(
                 [
                     'query_group_id',
                 ]
             )
             ->create();

        $this->table( 'query_groups' )
             ->addColumn( 'code', 'string', [
                 'default' => null,
                 'limit'   => 120,
                 'null'    => false,
             ] )
             ->addColumn( 'deleted', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'created', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'modified', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->create();

        $this->table( 'rootstocks' )
             ->addColumn( 'name', 'string', [
                 'default' => null,
                 'limit'   => 140,
                 'null'    => false,
             ] )
             ->create();

        $this->table( 'rows' )
             ->addColumn( 'code', 'string', [
                 'default' => null,
                 'limit'   => 45,
                 'null'    => false,
             ] )
             ->addColumn( 'note', 'text', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'date_created', 'date', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'date_eliminated', 'date', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'deleted', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'created', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'modified', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addIndex(
                 [
                     'code',
                 ]
             )
             ->create();

        $this->table( 'scions_bundles' )
             ->addColumn( 'code', 'string', [
                 'default' => null,
                 'limit'   => 45,
                 'null'    => false,
             ] )
             ->addColumn( 'numb_scions', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => true,
             ] )
             ->addColumn( 'date_scions_harvest', 'date', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'descents_publicid_list', 'string', [
                 'default' => null,
                 'limit'   => 140,
                 'null'    => true,
             ] )
             ->addColumn( 'note', 'text', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'external_use', 'boolean', [
                 'default' => false,
                 'limit'   => null,
                 'null'    => false,
             ] )
             ->addColumn( 'variety_id', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => false,
             ] )
             ->addColumn( 'deleted', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'created', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'modified', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addIndex(
                 [
                     'variety_id',
                 ]
             )
             ->addIndex(
                 [
                     'code',
                 ]
             )
             ->create();

        $this->table( 'trees' )
             ->addColumn( 'publicid', 'string', [
                 'default' => null,
                 'limit'   => 9,
                 'null'    => false,
             ] )
             ->addColumn( 'date_grafted', 'date', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'date_planted', 'date', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'date_eliminated', 'date', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'date_labeled', 'date', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'genuine_seedling', 'boolean', [
                 'default' => false,
                 'limit'   => null,
                 'null'    => false,
             ] )
             ->addColumn( 'migrated_tree', 'boolean', [
                 'default' => false,
                 'limit'   => null,
                 'null'    => false,
             ] )
             ->addColumn( 'offset', 'float', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'dont_eliminate', 'boolean', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'note', 'text', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'variety_id', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => false,
             ] )
             ->addColumn( 'rootstock_id', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => true,
             ] )
             ->addColumn( 'grafting_id', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => true,
             ] )
             ->addColumn( 'row_id', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => true,
             ] )
             ->addColumn( 'experiment_site_id', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => false,
             ] )
             ->addColumn( 'deleted', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'created', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'modified', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addIndex(
                 [
                     'experiment_site_id',
                 ]
             )
             ->addIndex(
                 [
                     'grafting_id',
                 ]
             )
             ->addIndex(
                 [
                     'rootstock_id',
                 ]
             )
             ->addIndex(
                 [
                     'row_id',
                 ]
             )
             ->addIndex(
                 [
                     'variety_id',
                 ]
             )
             ->addIndex(
                 [
                     'publicid',
                 ]
             )
             ->create();

        $this->table( 'users' )
             ->addColumn( 'email', 'string', [
                 'default' => null,
                 'limit'   => 255,
                 'null'    => false,
             ] )
             ->addColumn( 'password', 'string', [
                 'default' => null,
                 'limit'   => 255,
                 'null'    => false,
             ] )
             ->addColumn( 'level', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => false,
             ] )
             ->addColumn( 'time_zone', 'string', [
                 'default' => null,
                 'limit'   => 120,
                 'null'    => false,
             ] )
             ->addColumn( 'created', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'modified', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->create();

        $this->table( 'varieties' )
             ->addColumn( 'code', 'string', [
                 'default' => null,
                 'limit'   => 45,
                 'null'    => false,
             ] )
             ->addColumn( 'official_name', 'string', [
                 'default' => null,
                 'limit'   => 140,
                 'null'    => true,
             ] )
             ->addColumn( 'acronym', 'string', [
                 'default' => null,
                 'limit'   => 10,
                 'null'    => true,
             ] )
             ->addColumn( 'plant_breeder', 'string', [
                 'default' => null,
                 'limit'   => 255,
                 'null'    => true,
             ] )
             ->addColumn( 'registration', 'string', [
                 'default' => null,
                 'limit'   => 140,
                 'null'    => true,
             ] )
             ->addColumn( 'description', 'text', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'batch_id', 'integer', [
                 'default' => null,
                 'limit'   => 11,
                 'null'    => false,
             ] )
             ->addColumn( 'deleted', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'created', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addColumn( 'modified', 'datetime', [
                 'default' => null,
                 'limit'   => null,
                 'null'    => true,
             ] )
             ->addIndex(
                 [
                     'batch_id',
                 ]
             )
             ->addIndex(
                 [
                     'code',
                 ]
             )
             ->create();

        $this->table( 'batches' )
             ->addForeignKey(
                 'crossing_id',
                 'crossings',
                 'id',
                 [
                     'update' => 'NO_ACTION',
                     'delete' => 'NO_ACTION'
                 ]
             )
             ->update();

        $this->table( 'crossings' )
             ->addForeignKey(
                 'father_variety_id',
                 'varieties',
                 'id',
                 [
                     'update' => 'NO_ACTION',
                     'delete' => 'NO_ACTION'
                 ]
             )
             ->addForeignKey(
                 'mother_variety_id',
                 'varieties',
                 'id',
                 [
                     'update' => 'NO_ACTION',
                     'delete' => 'NO_ACTION'
                 ]
             )
             ->update();

        $this->table( 'mark_form_fields' )
             ->addForeignKey(
                 'mark_form_id',
                 'mark_forms',
                 'id',
                 [
                     'update' => 'NO_ACTION',
                     'delete' => 'NO_ACTION'
                 ]
             )
             ->addForeignKey(
                 'mark_form_property_id',
                 'mark_form_properties',
                 'id',
                 [
                     'update' => 'NO_ACTION',
                     'delete' => 'NO_ACTION'
                 ]
             )
             ->update();

        $this->table( 'mark_form_properties' )
             ->addForeignKey(
                 'mark_form_property_type_id',
                 'mark_form_property_types',
                 'id',
                 [
                     'update' => 'NO_ACTION',
                     'delete' => 'NO_ACTION'
                 ]
             )
             ->update();

        $this->table( 'mark_scanner_codes' )
             ->addForeignKey(
                 'mark_form_property_id',
                 'mark_form_properties',
                 'id',
                 [
                     'update' => 'NO_ACTION',
                     'delete' => 'NO_ACTION'
                 ]
             )
             ->update();

        $this->table( 'mark_values' )
             ->addForeignKey(
                 'mark_form_property_id',
                 'mark_form_properties',
                 'id',
                 [
                     'update' => 'NO_ACTION',
                     'delete' => 'NO_ACTION'
                 ]
             )
             ->addForeignKey(
                 'mark_id',
                 'marks',
                 'id',
                 [
                     'update' => 'NO_ACTION',
                     'delete' => 'NO_ACTION'
                 ]
             )
             ->update();

        $this->table( 'marks' )
             ->addForeignKey(
                 'batch_id',
                 'batches',
                 'id',
                 [
                     'update' => 'NO_ACTION',
                     'delete' => 'NO_ACTION'
                 ]
             )
             ->addForeignKey(
                 'mark_form_id',
                 'mark_forms',
                 'id',
                 [
                     'update' => 'NO_ACTION',
                     'delete' => 'NO_ACTION'
                 ]
             )
             ->addForeignKey(
                 'tree_id',
                 'trees',
                 'id',
                 [
                     'update' => 'NO_ACTION',
                     'delete' => 'NO_ACTION'
                 ]
             )
             ->addForeignKey(
                 'variety_id',
                 'varieties',
                 'id',
                 [
                     'update' => 'NO_ACTION',
                     'delete' => 'NO_ACTION'
                 ]
             )
             ->update();

        $this->table( 'mother_trees' )
             ->addForeignKey(
                 'crossing_id',
                 'crossings',
                 'id',
                 [
                     'update' => 'NO_ACTION',
                     'delete' => 'NO_ACTION'
                 ]
             )
             ->addForeignKey(
                 'tree_id',
                 'trees',
                 'id',
                 [
                     'update' => 'NO_ACTION',
                     'delete' => 'NO_ACTION'
                 ]
             )
             ->update();

        $this->table( 'queries' )
             ->addForeignKey(
                 'query_group_id',
                 'query_groups',
                 'id',
                 [
                     'update' => 'NO_ACTION',
                     'delete' => 'NO_ACTION'
                 ]
             )
             ->update();

        $this->table( 'scions_bundles' )
             ->addForeignKey(
                 'variety_id',
                 'varieties',
                 'id',
                 [
                     'update' => 'NO_ACTION',
                     'delete' => 'NO_ACTION'
                 ]
             )
             ->update();

        $this->table( 'trees' )
             ->addForeignKey(
                 'experiment_site_id',
                 'experiment_sites',
                 'id',
                 [
                     'update' => 'NO_ACTION',
                     'delete' => 'NO_ACTION'
                 ]
             )
             ->addForeignKey(
                 'grafting_id',
                 'graftings',
                 'id',
                 [
                     'update' => 'NO_ACTION',
                     'delete' => 'NO_ACTION'
                 ]
             )
             ->addForeignKey(
                 'rootstock_id',
                 'rootstocks',
                 'id',
                 [
                     'update' => 'NO_ACTION',
                     'delete' => 'NO_ACTION'
                 ]
             )
             ->addForeignKey(
                 'row_id',
                 'rows',
                 'id',
                 [
                     'update' => 'NO_ACTION',
                     'delete' => 'NO_ACTION'
                 ]
             )
             ->addForeignKey(
                 'variety_id',
                 'varieties',
                 'id',
                 [
                     'update' => 'NO_ACTION',
                     'delete' => 'NO_ACTION'
                 ]
             )
             ->update();

        $this->table( 'varieties' )
             ->addForeignKey(
                 'batch_id',
                 'batches',
                 'id',
                 [
                     'update' => 'NO_ACTION',
                     'delete' => 'NO_ACTION'
                 ]
             )
             ->update();

        /**
         * Handle views manually since Phinx can't handle them
         */
        $this->execute( "CREATE VIEW `batches_view` AS select `batches`.`id` AS `id`,concat(`crossings`.`code`,' . ',`batches`.`code`) AS `crossing_batch`,`batches`.`date_sowed` AS `date_sowed`,`batches`.`numb_seeds_sowed` AS `numb_seeds_sowed`,`batches`.`numb_sprouts_grown` AS `numb_sprouts_grown`,`batches`.`seed_tray` AS `seed_tray`,`batches`.`date_planted` AS `date_planted`,`batches`.`numb_sprouts_planted` AS `numb_sprouts_planted`,`batches`.`patch` AS `patch`,`batches`.`note` AS `note`,`batches`.`crossing_id` AS `crossing_id` from (`batches` join `crossings` on((`batches`.`crossing_id` = `crossings`.`id`))) where isnull(`batches`.`deleted`)" );
        $this->execute( "CREATE VIEW `varieties_view` AS select `varieties`.`id` AS `id`,concat(`batches_view`.`crossing_batch`,' . ',`varieties`.`code`) AS `convar`,`varieties`.`official_name` AS `official_name`,`varieties`.`acronym` AS `acronym`,`varieties`.`plant_breeder` AS `plant_breeder`,`varieties`.`registration` AS `registration`,`varieties`.`description` AS `description`,`varieties`.`batch_id` AS `batch_id` from (`varieties` join `batches_view` on((`varieties`.`batch_id` = `batches_view`.`id`))) where isnull(`varieties`.`deleted`)" );
        $this->execute( "CREATE VIEW `crossings_view` AS select `crossings`.`id` AS `id`,`crossings`.`code` AS `code`,`mother_varieties`.`convar` AS `mother_variety`,`father_varieties`.`convar` AS `father_variety`,`crossings`.`target` AS `target` from ((`crossings` left join `varieties_view` `mother_varieties` on((`crossings`.`mother_variety_id` = `mother_varieties`.`id`))) left join `varieties_view` `father_varieties` on((`crossings`.`father_variety_id` = `father_varieties`.`id`))) where isnull(`crossings`.`deleted`)" );
        $this->execute( "CREATE VIEW `marks_view` AS select `mark_values`.`id` AS `id`,`marks`.`date` AS `date`,`marks`.`author` AS `author`,`marks`.`tree_id` AS `tree_id`,`marks`.`variety_id` AS `variety_id`,`marks`.`batch_id` AS `batch_id`,`mark_values`.`value` AS `value`,`mark_values`.`exceptional_mark` AS `exceptional_mark`,`mark_form_properties`.`name` AS `name`,`mark_form_properties`.`id` AS `property_id`,`mark_form_properties`.`field_type` AS `field_type`,`mark_form_property_types`.`name` AS `property_type` from (((`marks` join `mark_values` on((`marks`.`id` = `mark_values`.`mark_id`))) join `mark_form_properties` on((`mark_values`.`mark_form_property_id` = `mark_form_properties`.`id`))) join `mark_form_property_types` on((`mark_form_properties`.`mark_form_property_type_id` = `mark_form_property_types`.`id`)))" );
        $this->execute( "CREATE VIEW `scions_bundles_view` AS select `scions_bundles`.`id` AS `id`,`scions_bundles`.`code` AS `identification`,`varieties_view`.`convar` AS `convar`,`scions_bundles`.`numb_scions` AS `numb_scions`,`scions_bundles`.`date_scions_harvest` AS `date_scions_harvest`,`scions_bundles`.`descents_publicid_list` AS `descents_publicid_list`,`scions_bundles`.`note` AS `note`,`scions_bundles`.`external_use` AS `external_use`,`scions_bundles`.`variety_id` AS `variety_id` from (`scions_bundles` join `varieties_view` on((`scions_bundles`.`variety_id` = `varieties_view`.`id`))) where isnull(`scions_bundles`.`deleted`)" );
        $this->execute( "CREATE VIEW `trees_view` AS select `trees`.`id` AS `id`,`trees`.`publicid` AS `publicid`,`varieties_view`.`convar` AS `convar`,`trees`.`date_grafted` AS `date_grafted`,`trees`.`date_planted` AS `date_planted`,`trees`.`date_eliminated` AS `date_eliminated`,`trees`.`date_labeled` AS `date_labeled`,`trees`.`genuine_seedling` AS `genuine_seedling`,`trees`.`offset` AS `offset`,`rows`.`code` AS `row`,`trees`.`dont_eliminate` AS `dont_eliminate`,`trees`.`note` AS `note`,`trees`.`variety_id` AS `variety_id`,`graftings`.`name` AS `grafting`,`rootstocks`.`name` AS `rootstock`,`experiment_sites`.`name` AS `experiment_site` from (((((`trees` left join `rows` on((`trees`.`row_id` = `rows`.`id`))) left join `graftings` on((`trees`.`grafting_id` = `graftings`.`id`))) left join `rootstocks` on((`trees`.`rootstock_id` = `rootstocks`.`id`))) join `experiment_sites` on((`trees`.`experiment_site_id` = `experiment_sites`.`id`))) join `varieties_view` on((`trees`.`variety_id` = `varieties_view`.`id`))) where isnull(`trees`.`deleted`)" );
        $this->execute( "CREATE VIEW `mother_trees_view` AS select `mother_trees`.`id` AS `id`,`crossings`.`code` AS `crossing`,`mother_trees`.`code` AS `code`,`mother_trees`.`planed` AS `planed`,`mother_trees`.`date_pollen_harvested` AS `date_pollen_harvested`,`mother_trees`.`date_impregnated` AS `date_impregnated`,`mother_trees`.`date_fruit_harvested` AS `date_fruit_harvested`,`mother_trees`.`numb_portions` AS `numb_portions`,`mother_trees`.`numb_flowers` AS `numb_flowers`,`mother_trees`.`numb_fruits` AS `numb_fruits`,`mother_trees`.`numb_seeds` AS `numb_seeds`,`mother_trees`.`note` AS `note`,`trees_view`.`publicid` AS `publicid`,`trees_view`.`convar` AS `convar`,`trees_view`.`offset` AS `offset`,`trees_view`.`row` AS `row`,`trees_view`.`experiment_site` AS `experiment_site`,`mother_trees`.`tree_id` AS `tree_id`,`mother_trees`.`crossing_id` AS `crossing_id` from ((`mother_trees` join `trees_view` on((`mother_trees`.`tree_id` = `trees_view`.`id`))) join `crossings` on((`mother_trees`.`crossing_id` = `crossings`.`id`))) where isnull(`mother_trees`.`deleted`)" );

        /**
         * Insert the necessary data to get going
         */
        // crossings
        $data  = [
            'id'                => '1',
            'code'              => 'SORTE',
            'mother_variety_id' => null,
            'father_variety_id' => null,
            'target'            => null,
            'deleted'           => null,
            'created'           => date( 'Y-m-d H:i:s' ),
            'modified'          => date( 'Y-m-d H:i:s' ),
        ];
        $table = $this->table( 'crossings' );
        $table->insert( $data )->save();

        // batches
        $data  = [
            'id'                   => '1',
            'code'                 => '000',
            'date_sowed'           => null,
            'numb_seeds_sowed'     => null,
            'numb_sprouts_grown'   => null,
            'seed_tray'            => null,
            'date_planted'         => null,
            'numb_sprouts_planted' => null,
            'patch'                => null,
            'note'                 => 'Reserviert für existierende Sorten',
            'crossing_id'          => '1',
            'deleted'              => null,
            'created'              => date( 'Y-m-d H:i:s' ),
            'modified'             => date( 'Y-m-d H:i:s' ),
        ];
        $table = $this->table( 'batches' );
        $table->insert( $data )->save();

        // users
        $hasher = new DefaultPasswordHasher();
        $data   = [
            'id'        => '1',
            'email'     => 'admin@breedersdb.com',
            'password'  => $hasher->hash( 'ChangeAfterLogin' ),
            'level'     => '0',
            'time_zone' => 'Europe/Brussels',
            'created'   => date( 'Y-m-d H:i:s' ),
            'modified'  => date( 'Y-m-d H:i:s' ),
        ];

        $table = $this->table( 'users' );
        $table->insert( $data )->save();

        // experiment sites
        $data = [
            'id'   => '1',
            'name' => 'Default Site',
        ];

        $table = $this->table( 'experiment_sites' );
        $table->insert( $data )->save();
    }

    public function down() {
        $this->table( 'batches' )
             ->dropForeignKey(
                 'crossing_id'
             )->save();

        $this->table( 'crossings' )
             ->dropForeignKey(
                 'father_variety_id'
             )
             ->dropForeignKey(
                 'mother_variety_id'
             )->save();

        $this->table( 'mark_form_fields' )
             ->dropForeignKey(
                 'mark_form_id'
             )
             ->dropForeignKey(
                 'mark_form_property_id'
             )->save();

        $this->table( 'mark_form_properties' )
             ->dropForeignKey(
                 'mark_form_property_type_id'
             )->save();

        $this->table( 'mark_scanner_codes' )
             ->dropForeignKey(
                 'mark_form_property_id'
             )->save();

        $this->table( 'mark_values' )
             ->dropForeignKey(
                 'mark_form_property_id'
             )
             ->dropForeignKey(
                 'mark_id'
             )->save();

        $this->table( 'marks' )
             ->dropForeignKey(
                 'batch_id'
             )
             ->dropForeignKey(
                 'mark_form_id'
             )
             ->dropForeignKey(
                 'tree_id'
             )
             ->dropForeignKey(
                 'variety_id'
             )->save();

        $this->table( 'mother_trees' )
             ->dropForeignKey(
                 'crossing_id'
             )
             ->dropForeignKey(
                 'tree_id'
             )->save();

        $this->table( 'queries' )
             ->dropForeignKey(
                 'query_group_id'
             )->save();

        $this->table( 'scions_bundles' )
             ->dropForeignKey(
                 'variety_id'
             )->save();

        $this->table( 'trees' )
             ->dropForeignKey(
                 'experiment_site_id'
             )
             ->dropForeignKey(
                 'grafting_id'
             )
             ->dropForeignKey(
                 'rootstock_id'
             )
             ->dropForeignKey(
                 'row_id'
             )
             ->dropForeignKey(
                 'variety_id'
             )->save();

        $this->table( 'varieties' )
             ->dropForeignKey(
                 'batch_id'
             )->save();

        $this->table( 'batches' )->drop()->save();
        $this->table( 'crossings' )->drop()->save();
        $this->table( 'experiment_sites' )->drop()->save();
        $this->table( 'graftings' )->drop()->save();
        $this->table( 'mark_form_fields' )->drop()->save();
        $this->table( 'mark_form_properties' )->drop()->save();
        $this->table( 'mark_form_property_types' )->drop()->save();
        $this->table( 'mark_forms' )->drop()->save();
        $this->table( 'mark_scanner_codes' )->drop()->save();
        $this->table( 'mark_values' )->drop()->save();
        $this->table( 'marks' )->drop()->save();
        $this->table( 'mother_trees' )->drop()->save();
        $this->table( 'queries' )->drop()->save();
        $this->table( 'query_groups' )->drop()->save();
        $this->table( 'rootstocks' )->drop()->save();
        $this->table( 'rows' )->drop()->save();
        $this->table( 'scions_bundles' )->drop()->save();
        $this->table( 'trees' )->drop()->save();
        $this->table( 'users' )->drop()->save();
        $this->table( 'varieties' )->drop()->save();

        /**
         * Handle views manually since Phinx can't handle them
         */
        $this->execute( 'DROP VIEW `batches_view`' );
        $this->execute( 'DROP VIEW `crossings_view`' );
        $this->execute( 'DROP VIEW `marks_view`' );
        $this->execute( 'DROP VIEW `mother_trees_view`' );
        $this->execute( 'DROP VIEW `scions_bundles_view`' );
        $this->execute( 'DROP VIEW `trees_view`' );
        $this->execute( 'DROP VIEW `varieties_view`' );
    }
}
