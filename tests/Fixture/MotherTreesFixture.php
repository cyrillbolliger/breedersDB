<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use App\Generator\MotherTreesGenerator;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * MotherTreesFixture
 */
class MotherTreesFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $generator = new MotherTreesGenerator();
        $this->records = $generator->generate(100);
        parent::init();
    }
}
