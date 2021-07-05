<?php

use App\Generator\ScionsBundlesGenerator;
use Migrations\AbstractSeed;

/**
 * ScionsBundles seed.
 */
class ScionsBundlesSeed extends AbstractSeed
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
        $generator = new ScionsBundlesGenerator();
        $data = $generator->generate(15);

        $table = $this->table('scions_bundles');
        $table->insert($data)->save();
    }
}
