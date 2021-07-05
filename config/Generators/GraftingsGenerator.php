<?php
namespace App\Generator;


/**
 * Graftings generator.
 */
class GraftingsGenerator
{

    public function generate(int $count)
    {
        $faker = \Faker\Factory::create();
        $data = [];
        for ($i = 0; $i < $count; $i++) {
            $data[] = [
                'name' => $faker->word,
            ];
        }

        return $data;
    }
}
