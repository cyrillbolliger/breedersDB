<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TreesFixture
 */
class TreesFixture extends TestFixture
{
    /**
     * Fields
     *
     * @var array
     */
    // phpcs:disable
    public $fields = [
        'id' => ['type' => 'integer', 'length' => null, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'publicid' => ['type' => 'char', 'length' => 9, 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => '', 'precision' => null],
        'date_grafted' => ['type' => 'date', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'date_planted' => ['type' => 'date', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'date_eliminated' => ['type' => 'date', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'date_labeled' => ['type' => 'date', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'genuine_seedling' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'migrated_tree' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'offset' => ['type' => 'float', 'length' => null, 'precision' => null, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => ''],
        'dont_eliminate' => ['type' => 'boolean', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'note' => ['type' => 'text', 'length' => null, 'null' => true, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => '', 'precision' => null],
        'variety_id' => ['type' => 'integer', 'length' => null, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'rootstock_id' => ['type' => 'integer', 'length' => null, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'grafting_id' => ['type' => 'integer', 'length' => null, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'row_id' => ['type' => 'integer', 'length' => null, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'experiment_site_id' => ['type' => 'integer', 'length' => null, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'deleted' => ['type' => 'datetime', 'length' => null, 'precision' => null, 'null' => true, 'default' => null, 'comment' => ''],
        'created' => ['type' => 'datetime', 'length' => null, 'precision' => null, 'null' => true, 'default' => null, 'comment' => ''],
        'modified' => ['type' => 'datetime', 'length' => null, 'precision' => null, 'null' => true, 'default' => null, 'comment' => ''],
        'convar' => ['type' => 'string', 'length' => 58, 'null' => false, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => '', 'precision' => null],
        '_indexes' => [
            'fk_trees_clones1_idx' => ['type' => 'index', 'columns' => ['variety_id'], 'length' => []],
            'fk_trees_rootstocks1_idx' => ['type' => 'index', 'columns' => ['rootstock_id'], 'length' => []],
            'fk_trees_graftings1_idx' => ['type' => 'index', 'columns' => ['grafting_id'], 'length' => []],
            'fk_trees_rows1_idx' => ['type' => 'index', 'columns' => ['row_id'], 'length' => []],
            'fk_trees_sites1_idx' => ['type' => 'index', 'columns' => ['experiment_site_id'], 'length' => []],
            'publicid_idx' => ['type' => 'index', 'columns' => ['publicid'], 'length' => []],
            'convar' => ['type' => 'index', 'columns' => ['convar'], 'length' => []],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
            'id_UNIQUE' => ['type' => 'unique', 'columns' => ['id'], 'length' => []],
            'fk_trees_sites1' => ['type' => 'foreign', 'columns' => ['experiment_site_id'], 'references' => ['experiment_sites', 'id'], 'update' => 'noAction', 'delete' => 'noAction', 'length' => []],
            'fk_trees_rows1' => ['type' => 'foreign', 'columns' => ['row_id'], 'references' => ['rows', 'id'], 'update' => 'noAction', 'delete' => 'noAction', 'length' => []],
            'fk_trees_rootstocks1' => ['type' => 'foreign', 'columns' => ['rootstock_id'], 'references' => ['rootstocks', 'id'], 'update' => 'noAction', 'delete' => 'noAction', 'length' => []],
            'fk_trees_graftings1' => ['type' => 'foreign', 'columns' => ['grafting_id'], 'references' => ['graftings', 'id'], 'update' => 'noAction', 'delete' => 'noAction', 'length' => []],
            'fk_trees_clones1' => ['type' => 'foreign', 'columns' => ['variety_id'], 'references' => ['varieties', 'id'], 'update' => 'noAction', 'delete' => 'noAction', 'length' => []],
        ],
        '_options' => [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_general_ci'
        ],
    ];
    // phpcs:enable
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'publicid' => '',
                'date_grafted' => '2021-02-27',
                'date_planted' => '2021-02-27',
                'date_eliminated' => '2021-02-27',
                'date_labeled' => '2021-02-27',
                'genuine_seedling' => 1,
                'migrated_tree' => 1,
                'offset' => 1,
                'dont_eliminate' => 1,
                'note' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'variety_id' => 1,
                'rootstock_id' => 1,
                'grafting_id' => 1,
                'row_id' => 1,
                'experiment_site_id' => 1,
                'deleted' => '2021-02-27 19:16:14',
                'created' => '2021-02-27 19:16:14',
                'modified' => '2021-02-27 19:16:14',
                'convar' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}
