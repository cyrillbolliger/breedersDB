<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use App\Generator\RootstocksGenerator;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * RootstocksFixture
 */
class RootstocksFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $generator = new RootstocksGenerator();
        $this->records = $generator->generate();
        parent::init();
    }
}
