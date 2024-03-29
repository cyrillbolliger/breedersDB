<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use App\Generator\ScionsBundlesGenerator;
use App\Test\Util\DependsOnFixtureTrait;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * ScionsBundlesFixture
 */
class ScionsBundlesFixture extends TestFixture
{
    use DependsOnFixtureTrait;

    protected array $dependsOnFixture = ['Varieties'];


    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $generator = new ScionsBundlesGenerator();
        $this->records = $generator->generate(200);
        parent::init();
    }
}
