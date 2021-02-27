<?php
namespace App\Generator;


/**
 * Crossings generator.
 */
class CrossingsGenerator
{

    public function generate(int $count)
    {
        $faker = \Faker\Factory::create();
        $data = [];
        for ($i = 0; $i < $count; $i++) {
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

        return $data;
    }
}
