<?php

use App\Generator\SettingsGenerator;
use Migrations\AbstractSeed;

/**
 * Settings seed.
 */
class SettingsSeed extends AbstractSeed
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
        $generator = new SettingsGenerator();
        $data = $generator->generate();

        $table = $this->table('settings');
        $table->insert($data)->save();
    }
}
