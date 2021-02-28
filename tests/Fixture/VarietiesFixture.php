<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use App\Generator\VarietiesGenerator;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * VarietiesFixture
 */
class VarietiesFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $generator = new VarietiesGenerator();
        $this->records = $generator->generate(200);
        parent::init();
    }
}
