<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use App\Generator\BatchesGenerator;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * BatchesFixture
 */
class BatchesFixture extends TestFixture
{
    use DependsOnFixtureTrait;

    protected array $dependsOnFixture = ['Crossings'];

    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $generator = new BatchesGenerator();
        $this->records = $generator->generate(200);
        parent::init();
    }
}
