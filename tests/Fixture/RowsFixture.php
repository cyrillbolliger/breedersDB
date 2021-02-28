<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use App\Generator\RowsGenerator;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * RowsFixture
 */
class RowsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $generator = new RowsGenerator();
        $this->records = $generator->generate(50);
        parent::init();
    }
}
