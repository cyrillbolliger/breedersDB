<?php

use App\Generator\ExperimentSitesGenerator;
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
        $generator = new ExperimentSitesGenerator();
        $data = $generator->generate(3);

        $table = $this->table('experiment_sites');
        $table->insert($data)->save();
    }
}
