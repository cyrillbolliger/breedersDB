<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use App\Generator\SettingsGenerator;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * SettingsFixture
 */
class SettingsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $generator = new SettingsGenerator();
        $this->records = $generator->generate();
        parent::init();
    }
}
