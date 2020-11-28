<?php
use Migrations\AbstractSeed;

/**
 * Master seeder to populate the database with demo data
 *
 * Due to the foreign key constraints, the individual seeders have to be called
 * in a specific order. This is a convenience seeder that temporarily disables
 * the foreign key checks, so seeding works even with circular references. But
 * be careful, no checks means things can break ;)
 */
class DemoSeed extends AbstractSeed
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
        $this->execute('SET FOREIGN_KEY_CHECKS=0;');

        $this->call('RowsSeed');
        $this->call('GraftingsSeed');
        $this->call('RootstocksSeed');
        $this->call('ExperimentSitesSeed');
        $this->call('UsersSeed');
        $this->call('CrossingsSeed');
        $this->call('BatchesSeed');
        $this->call('VarietiesSeed');
        $this->call('TreesSeed');
        $this->call('ScionsBundlesSeed');
        $this->call('MotherTreesSeed');

        $this->execute('SET FOREIGN_KEY_CHECKS=1;');
    }
}
