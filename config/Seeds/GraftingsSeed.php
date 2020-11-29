<?php
use Migrations\AbstractSeed;

/**
 * Graftings seed.
 */
class GraftingsSeed extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeds is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'id' => '1',
                'name' => 'WHV',
            ],
            [
                'id' => '2',
                'name' => 'Okulation',
            ],
            [
                'id' => '3',
                'name' => 'Pfropfung',
            ],
        ];

        $table = $this->table('graftings');
        $table->insert($data)->save();
    }
}
