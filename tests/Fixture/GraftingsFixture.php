<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use App\Generator\GraftingsGenerator;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * GraftingsFixture
 */
class GraftingsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $generator = new GraftingsGenerator();
        $this->records = $generator->generate(5);
        parent::init();
    }
}
