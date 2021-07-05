<?php
namespace App\Generator;

/**
 * QueryGroups generator.
 */
class QueryGroupsGenerator
{

    public function generate(int $count)
    {
        $faker = \Faker\Factory::create();

        $data = [];
        for ($i = 0; $i < $count; $i++) {
            $date = $faker->dateTimeBetween('-5 years', 'now');
            $data[] = [
                'code' => $faker->word,
                'deleted' => NULL,
                'created' => $date->format('Y-m-d H:i:s'),
                'modified' => $date->format('Y-m-d H:i:s'),
            ];
        }

        return $data;
    }
}
