<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use App\Generator\VarietiesGenerator;
use App\Test\Util\DependsOnFixtureTrait;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * VarietiesFixture
 */
class VarietiesFixture extends TestFixture
{
    use DependsOnFixtureTrait;

    protected array $dependsOnFixture = ['Batches'];

    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->table = $this->getTable('Varieties');
        $generator = new VarietiesGenerator();
        $this->records = $generator->generate(200);
        parent::init();
    }
}
