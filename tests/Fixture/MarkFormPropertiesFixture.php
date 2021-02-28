<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use App\Generator\MarkFormPropertiesGenerator;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * MarkFormPropertiesFixture
 */
class MarkFormPropertiesFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $generator = new MarkFormPropertiesGenerator();
        $this->records = $generator->generate();
        parent::init();
    }
}
