<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use App\Generator\MarkFormFieldsGenerator;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * MarkFormFieldsFixture
 */
class MarkFormFieldsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $generator = new MarkFormFieldsGenerator();
        $this->records = $generator->generate();
        parent::init();
    }
}
