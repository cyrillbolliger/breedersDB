<?php
use Migrations\AbstractSeed;

/**
 * Batches seed.
 */
class BatchesSeed extends AbstractSeed
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
                'code' => sprintf('%02d%s', $faker->numberBetween(16,25), $faker->randomElement(['A','B','C'])),
                'date_sowed' => $faker->dateTimeBetween('-1 year', '-9 months')->format('Y-m-d'),
                'numb_seeds_sowed' => $faker->numberBetween(50, 100),
                'numb_sprouts_grown' => $faker->numberBetween(25, 50),
                'seed_tray' => $faker->numberBetween(12, 37),
                'date_planted' => $faker->dateTimeBetween('-9 year', '-6 months')->format('Y-m-d'),
                'numb_sprouts_planted' => $faker->numberBetween(1, 25),
                'patch' => $faker->word(),
                'note' => $faker->sentence(),
                'crossing_id' => $faker->numberBetween(2, 99),
                'deleted' => NULL,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ];
        }

        $table = $this->table('batches');
        $table->insert($data)->save();
    }
}
