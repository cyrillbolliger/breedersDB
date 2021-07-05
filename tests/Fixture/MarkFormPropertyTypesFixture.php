<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use App\Generator\MarkFormPropertyTypesGenerator;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * MarkFormPropertyTypesFixture
 */
class MarkFormPropertyTypesFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $generator = new MarkFormPropertyTypesGenerator();
        $this->records = $generator->generate();
        parent::init();
    }
}
