<?php
use Migrations\AbstractSeed;

/**
 * MotherTrees seed.
 */
class MotherTreesSeed extends AbstractSeed
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
        $start = new DateTime('-1 year');
        $flowers = $faker->numberBetween(20,80);
        $data = [];
        for ($i = 0; $i < 100; $i++) {
            $crossing = $faker->unique()->regexify('[A-Z]{1,2}[A-Za-z0-9]{3}[A-Z]{1}[A-Za-z0-9]{1,2}');
            $data[] = [
                'code' => sprintf('%04d_%s', $faker->unique()->numberBetween(1,1999), $crossing),
                'planed' => '0',
                'date_pollen_harvested' => $start->sub(new DateInterval('P1D'))->format('Y-m-d'),
                'date_impregnated' => $start->format('Y-m-d'),
                'date_fruit_harvested' => $start->add(new DateInterval('P5M'))->format('Y-m-d'),
                'numb_portions' => $faker->numberBetween(10,30),
                'numb_flowers' => $flowers,
                'numb_fruits' => (int) $flowers*0.7,
                'numb_seeds' => $flowers*5,
                'note' => $faker->sentence(),
                'tree_id' => $faker->numberBetween(1,1999),
                'crossing_id' => $faker->numberBetween(2,99),
                'deleted' => NULL,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ];
        }

        $table = $this->table('mother_trees');
        $table->insert($data)->save();
    }
}
