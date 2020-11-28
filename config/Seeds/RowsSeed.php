<?php
use Migrations\AbstractSeed;

/**
 * Rows seed.
 */
class RowsSeed extends AbstractSeed
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
        for ($i = 0; $i < 15; $i++) {
            $data[] = [
                'code' => sprintf('%s%03d', $faker->randomElement(['A','B','C']), $faker->unique()->numberBetween(1,100)),
                'note' => $faker->sentence(),
                'date_created' => $faker->dateTimeThisYear('-1 month')->format('Y-m-d'),
                'deleted' => NULL,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ];
        }

        $table = $this->table('rows');
        $table->insert($data)->save();
    }
}
