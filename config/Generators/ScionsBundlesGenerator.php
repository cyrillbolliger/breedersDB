<?php
namespace App\Generator;

/**
 * ScionsBundles generator.
 */
class ScionsBundlesGenerator
{

    public function generate(int $count)
    {
        $faker = \Faker\Factory::create();
        $data = [];
        for ($i = 0; $i < $count; $i++) {
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

        return $data;
    }
}
