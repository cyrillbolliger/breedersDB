<?php
use Migrations\AbstractSeed;

/**
 * ScionsBundles seed.
 */
class ScionsBundlesSeed extends AbstractSeed
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
        $faker = Faker\Factory::create();
        $data = [];
        for ($i = 0; $i < 15; $i++) {
            $data[] = [
                'code' => 'BD_'.$faker->unique()->numberBetween(11, 65),
                'numb_scions' => $faker->numberBetween(2,22),
                'date_scions_harvest' => $faker->dateTimeThisYear()->format('Y-m-d'),
                'descents_publicid_list' => sprintf('%08d, %08d, %08d', 152 + $i, 231 + $i, 15 + $i),
                'note' => $faker->name,
                'external_use' => '1',
                'variety_id' => $faker->numberBetween(11,999),
                'deleted' => NULL,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ];
        }

        $table = $this->table('scions_bundles');
        $table->insert($data)->save();
    }
}
