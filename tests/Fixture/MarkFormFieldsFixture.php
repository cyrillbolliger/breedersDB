<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use App\Generator\MarkFormFieldsGenerator;
use App\Test\Util\DependsOnFixtureTrait;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * MarkFormFieldsFixture
 */
class MarkFormFieldsFixture extends TestFixture
{
    use DependsOnFixtureTrait;

    protected array $dependsOnFixture = [ 'MarkForms', 'MarkFormProperties' ];

    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->table = $this->getTable('MarkFormFields');
        $generator = new MarkFormFieldsGenerator();
        $this->records = $generator->generate();
        parent::init();
    }
}
