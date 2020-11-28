<?php
use Migrations\AbstractSeed;

/**
 * ExperimentSites seed.
 */
class ExperimentSitesSeed extends AbstractSeed
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
        for ($i = 0; $i < 2; $i++) {
            $data[] = [
                'name' => $faker->city,
            ];
        }

        $table = $this->table('experiment_sites');
        $table->insert($data)->save();
    }
}
