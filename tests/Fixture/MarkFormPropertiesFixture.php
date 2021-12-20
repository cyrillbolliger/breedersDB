<?php
declare( strict_types=1 );

namespace App\Test\Fixture;

use App\Generator\MarkFormPropertiesGenerator;
use App\Test\Util\DependsOnFixtureTrait;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * MarkFormPropertiesFixture
 */
class MarkFormPropertiesFixture extends TestFixture {

    use DependsOnFixtureTrait;

    protected array $dependsOnFixture = [ 'MarkFormPropertyTypes' ];

    /**
     * Init method
     *
     * @return void
     */
    public function init(): void {
        $this->table = $this->getTable('MarkFormProperties');
        $generator     = new MarkFormPropertiesGenerator();
        $this->records = $generator->generate();
        parent::init();
    }
}
