<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use App\Generator\MarkFormsGenerator;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * MarkFormsFixture
 */
class MarkFormsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $generator = new MarkFormsGenerator();
        $this->records = $generator->generate();
        parent::init();
    }
}
