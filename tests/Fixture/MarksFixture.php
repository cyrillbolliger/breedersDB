<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use App\Generator\MarksGenerator;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * MarksFixture
 */
class MarksFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $generator = new MarksGenerator();
        $this->records = $generator->generate();
        parent::init();
    }
}
