<?php
namespace App\Generator;


/**
 * ExperimentSites generator.
 */
class ExperimentSitesGenerator
{

    public function generate(int $count)
    {
        $faker = \Faker\Factory::create();
        $data = [];
        for ($i = 0; $i < $count; $i++) {
            $data[] = [
                'name' => $faker->city,
            ];
        }

        return $data;
    }
}
