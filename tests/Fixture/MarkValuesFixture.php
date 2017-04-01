<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MarkValuesFixture
 *
 */
class MarkValuesFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'value' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'exceptional_mark' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'mark_form_property_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'mark_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        '_indexes' => [
            'fk_rating_values_rating_form_properties1_idx' => ['type' => 'index', 'columns' => ['mark_form_property_id'], 'length' => []],
            'fk_rating_values_ratings1_idx' => ['type' => 'index', 'columns' => ['mark_id'], 'length' => []],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
            'id_UNIQUE' => ['type' => 'unique', 'columns' => ['id'], 'length' => []],
            'fk_rating_values_rating_form_properties1' => ['type' => 'foreign', 'columns' => ['mark_form_property_id'], 'references' => ['mark_form_properties', 'id'], 'update' => 'noAction', 'delete' => 'noAction', 'length' => []],
            'fk_rating_values_ratings1' => ['type' => 'foreign', 'columns' => ['mark_id'], 'references' => ['marks', 'id'], 'update' => 'noAction', 'delete' => 'noAction', 'length' => []],
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
            'value' => 'Lorem ipsum dolor sit amet',
            'exceptional_mark' => 1,
            'mark_form_property_id' => 1,
            'mark_id' => 1,
            'created' => '2017-01-18 14:49:41',
            'modified' => '2017-01-18 14:49:41'
        ],
    ];
}
