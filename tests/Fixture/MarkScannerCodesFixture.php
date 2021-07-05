<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use App\Generator\MarkScannerCodesGenerator;
use App\Test\Util\DependsOnFixtureTrait;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * MarkScannerCodesFixture
 */
class MarkScannerCodesFixture extends TestFixture
{
    use DependsOnFixtureTrait;

    protected array $dependsOnFixture = [ 'MarkFormProperties' ];

    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $generator = new MarkScannerCodesGenerator();
        $this->records = $generator->generate();
        parent::init();
    }
}
