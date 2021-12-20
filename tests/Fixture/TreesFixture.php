<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use App\Generator\TreesGenerator;
use App\Test\Util\DependsOnFixtureTrait;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * TreesFixture
 */
class TreesFixture extends TestFixture
{
    use DependsOnFixtureTrait;

    protected array $dependsOnFixture = ['Varieties', 'Rootstocks', 'Graftings', 'Rows', 'ExperimentSites'];

    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->table = $this->getTable('Trees');
        $generator = new TreesGenerator();
        $this->records = $generator->generate(200);
        parent::init();
    }
}
