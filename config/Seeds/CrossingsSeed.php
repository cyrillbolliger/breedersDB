<?php
use Migrations\AbstractSeed;

/**
 * Crossings seed.
 */
class CrossingsSeed extends AbstractSeed
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
        for ($i = 0; $i < 100; $i++) {
            $data[] = [
                'code' => $faker->unique()->regexify('[A-Z]{1,2}[A-Za-z0-9]{3}[A-Z]{1}[A-Za-z0-9]{1,2}'),
//                'mother_variety_id' => $faker->numberBetween(2, 99),
//                'father_variety_id' => $faker->numberBetween(2, 99),
                'target' => $faker->sentence(),
                'deleted' => NULL,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ];
        }

        $table = $this->table('crossings');
        $table->insert($data)->save();
    }
}
