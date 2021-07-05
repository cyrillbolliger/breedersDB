<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use App\Generator\ExperimentSitesGenerator;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * ExperimentSitesFixture
 */
class ExperimentSitesFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $generator = new ExperimentSitesGenerator();
        $this->records = $generator->generate(3);
        parent::init();
    }
}
