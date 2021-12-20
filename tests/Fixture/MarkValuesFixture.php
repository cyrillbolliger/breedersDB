<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use App\Generator\MarkValuesGenerator;
use App\Test\Util\DependsOnFixtureTrait;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * MarkValuesFixture
 */
class MarkValuesFixture extends TestFixture
{
    use DependsOnFixtureTrait;

    protected array $dependsOnFixture = ['Marks'];

    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->table = $this->getTable('MarkValues');
        $generator = new MarkValuesGenerator();
        $this->records = $generator->generate();
        parent::init();
    }
}
