<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MarkFormFieldsFixture
 *
 */
class MarkFormFieldsFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'priority' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'mark_form_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'mark_form_property_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        '_indexes' => [
            'fk_rating_form_fields_rating_forms1_idx' => ['type' => 'index', 'columns' => ['mark_form_id'], 'length' => []],
            'fk_rating_form_fields_rating_form_properties1_idx' => ['type' => 'index', 'columns' => ['mark_form_property_id'], 'length' => []],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
            'id_UNIQUE' => ['type' => 'unique', 'columns' => ['id'], 'length' => []],
            'fk_rating_form_fields_rating_form_properties1' => ['type' => 'foreign', 'columns' => ['mark_form_property_id'], 'references' => ['mark_form_properties', 'id'], 'update' => 'noAction', 'delete' => 'noAction', 'length' => []],
            'fk_rating_form_fields_rating_forms1' => ['type' => 'foreign', 'columns' => ['mark_form_id'], 'references' => ['mark_forms', 'id'], 'update' => 'noAction', 'delete' => 'noAction', 'length' => []],
        ],
        '_options' => [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_general_ci'
        ],
    ];
    // @codingStandardsIgnoreEnd

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'id' => 1,
            'priority' => 1,
            'mark_form_id' => 1,
            'mark_form_property_id' => 1,
            'created' => '2017-01-17 16:30:53',
            'modified' => '2017-01-17 16:30:53'
        ],
    ];
}
