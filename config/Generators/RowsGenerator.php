<?php
namespace App\Generator;

/**
 * Rows generator.
 */
class RowsGenerator
{

    public function generate(int $count)
    {
        $faker = \Faker\Factory::create();
        $data = [];
        for ($i = 0; $i < $count; $i++) {
            $data[] = [
                'code' => sprintf('%s%03d', $faker->randomElement(['A','B','C']), $faker->unique()->numberBetween(1,100)),
                'note' => $faker->sentence(),
                'date_created' => $faker->dateTimeThisYear('-1 month')->format('Y-m-d'),
                'deleted' => NULL,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ];
        }

        return $data;
    }
}
