<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MarksFixture
 */
class MarksFixture extends TestFixture
{
    /**
     * Fields
     *
     * @var array
     */
    // phpcs:disable
    public $fields = [
        'id' => ['type' => 'integer', 'length' => null, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'date' => ['type' => 'date', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'author' => ['type' => 'string', 'length' => 45, 'null' => true, 'default' => null, 'collate' => 'utf8mb4_general_ci', 'comment' => '', 'precision' => null],
        'mark_form_id' => ['type' => 'integer', 'length' => null, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'tree_id' => ['type' => 'integer', 'length' => null, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'variety_id' => ['type' => 'integer', 'length' => null, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'batch_id' => ['type' => 'integer', 'length' => null, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'precision' => null, 'null' => true, 'default' => null, 'comment' => ''],
        'modified' => ['type' => 'datetime', 'length' => null, 'precision' => null, 'null' => true, 'default' => null, 'comment' => ''],
        '_indexes' => [
            'fk_ratings_rating_forms1_idx' => ['type' => 'index', 'columns' => ['mark_form_id'], 'length' => []],
            'fk_ratings_trees1_idx' => ['type' => 'index', 'columns' => ['tree_id'], 'length' => []],
            'fk_ratings_batches1_idx' => ['type' => 'index', 'columns' => ['batch_id'], 'length' => []],
            'fk_ratings_clones1_idx' => ['type' => 'index', 'columns' => ['variety_id'], 'length' => []],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
            'id_UNIQUE' => ['type' => 'unique', 'columns' => ['id'], 'length' => []],
            'fk_ratings_trees1' => ['type' => 'foreign', 'columns' => ['tree_id'], 'references' => ['trees', 'id'], 'update' => 'noAction', 'delete' => 'noAction', 'length' => []],
            'fk_ratings_rating_forms1' => ['type' => 'foreign', 'columns' => ['mark_form_id'], 'references' => ['mark_forms', 'id'], 'update' => 'noAction', 'delete' => 'noAction', 'length' => []],
            'fk_ratings_clones1' => ['type' => 'foreign', 'columns' => ['variety_id'], 'references' => ['varieties', 'id'], 'update' => 'noAction', 'delete' => 'noAction', 'length' => []],
            'fk_ratings_batches1' => ['type' => 'foreign', 'columns' => ['batch_id'], 'references' => ['batches', 'id'], 'update' => 'noAction', 'delete' => 'noAction', 'length' => []],
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
                'date' => '2021-02-27',
                'author' => 'Lorem ipsum dolor sit amet',
                'mark_form_id' => 1,
                'tree_id' => 1,
                'variety_id' => 1,
                'batch_id' => 1,
                'created' => '2021-02-27 19:16:14',
                'modified' => '2021-02-27 19:16:14',
            ],
        ];
        parent::init();
    }
}
