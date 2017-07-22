<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * CrossingsViewFixture
 *
 */
class CrossingsViewFixture extends TestFixture
{

    /**
     * Table name
     *
     * @var string
     */
    public $table = 'crossings_view';

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'code' => ['type' => 'string', 'length' => 8, 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'mother_variety' => ['type' => 'string', 'length' => 58, 'null' => true, 'default' => '', 'collate' => 'utf8mb4_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'father_variety' => ['type' => 'string', 'length' => 58, 'null' => true, 'default' => '', 'collate' => 'utf8mb4_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        '_options' => [
            'engine' => null,
            'collation' => null
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
            'code' => 'Lorem ',
            'mother_variety' => 'Lorem ipsum dolor sit amet',
            'father_variety' => 'Lorem ipsum dolor sit amet'
        ],
    ];
}
