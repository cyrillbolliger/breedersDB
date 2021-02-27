<?php
namespace App\Generator;

use DateInterval;

/**
 * Trees generator.
 */
class TreesGenerator
{

    public function generate(int $count)
    {
        $faker = \Faker\Factory::create();
        $data = [];
        for ($i = 0; $i < $count; $i++) {
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

        return $data;
    }
}
