<?php
use Migrations\AbstractSeed;

/**
 * Varieties seed.
 */
class VarietiesSeed extends AbstractSeed
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
        // official varieties
        for ($i = 0; $i < 10; $i++) {
            $word = $faker->unique()->word();
            $data[] = [
                'code' => strtolower($word),
                'official_name' => ucfirst($word),
                'acronym' => ucfirst(substr($word, 0, 3)),
                'plant_breeder' => $faker->name,
                'registration' => NULL,
                'description' => $faker->sentence(),
                'batch_id' => '1',
                'deleted' => NULL,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ];
        }

        // breeder varieties
        for ($i = 10; $i < 1000; $i++) {
            $data[] = [
                'code' => sprintf('%03d', $faker->numberBetween(1, 99)),
                'official_name' => '',
                'acronym' => '',
                'plant_breeder' => '',
                'registration' => NULL,
                'description' => $faker->sentence(),
                'batch_id' => $faker->numberBetween(2,99),
                'deleted' => NULL,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ];
        }

        $table = $this->table('varieties');
        $table->insert($data)->save();
    }
}
