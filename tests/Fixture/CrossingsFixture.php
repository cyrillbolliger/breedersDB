<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use App\Generator\CrossingsGenerator;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * CrossingsFixture
 */
class CrossingsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $generator = new CrossingsGenerator();
        $this->records = $generator->generate(200);
        parent::init();
    }
}
