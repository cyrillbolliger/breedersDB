<?php
use Migrations\AbstractSeed;

/**
 * Trees seed.
 */
class TreesSeed extends AbstractSeed
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
        for ($i = 0; $i < 2000; $i++) {
            $grafted = $faker->dateTimeThisYear('-1 month');
            $data[] = [
                'publicid' => sprintf('%08d', 152 + $i),
                'date_grafted' => $grafted->format('Y-m-d'),
                'date_planted' => $grafted->add(new DateInterval('P2D'))->format('Y-m-d'),
                'date_eliminated' => NULL,
                'date_labeled' => $grafted->add(new DateInterval('P1D'))->format('Y-m-d'),
                'genuine_seedling' => '0',
                'migrated_tree' => '0',
                'offset' => $faker->randomFloat(1, 0, 127),
                'dont_eliminate' => NULL,
                'note' => $faker->sentence(),
                'variety_id' => $faker->numberBetween(10, 999),
                'rootstock_id' => $faker->numberBetween(1, 2),
                'grafting_id' => $faker->numberBetween(1, 3),
                'row_id' => $faker->numberBetween(1, 10),
                'experiment_site_id' => $faker->numberBetween(1, 3),
                'deleted' => NULL,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ];
        }

        $table = $this->table('trees');
        $table->insert($data)->save();
    }
}
