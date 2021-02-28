<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use App\Generator\MarkValuesGenerator;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * MarkValuesFixture
 */
class MarkValuesFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $generator = new MarkValuesGenerator();
        $this->records = $generator->generate();
        parent::init();
    }
}
