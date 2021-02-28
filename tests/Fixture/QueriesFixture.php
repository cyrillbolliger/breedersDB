<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use App\Generator\QueriesGenerator;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * QueriesFixture
 */
class QueriesFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $generator = new QueriesGenerator();
        $this->records = $generator->generate();
        parent::init();
    }
}
