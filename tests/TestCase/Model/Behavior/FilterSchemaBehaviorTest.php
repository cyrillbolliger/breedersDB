<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Behavior;

use App\Model\Behavior\FilterSchemaBehavior;
use App\Test\Util\DependsOnFixtureTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Behavior\SchemaBehavior Test Case
 */
class FilterSchemaBehaviorTest extends TestCase
{
    use DependsOnFixtureTrait;

    protected array $dependsOnFixture = [
        'Varieties',
        'Rows',
        'ExperimentSites',
        'Rootstocks',
        'Graftings',
        'Trees',
    ];

    /**
     * Test subject
     *
     * @var FilterSchemaBehavior
     */
    protected FilterSchemaBehavior $SchemaBehavior;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $table = \Cake\Datasource\FactoryLocator::get('Table')->get( 'TreesView' );
        $this->SchemaBehavior = new FilterSchemaBehavior($table);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->SchemaBehavior);

        parent::tearDown();
    }

    /**
     * Test getSchema method
     *
     * @return void
     * @uses \App\Model\Behavior\SchemaBehavior::getSchema()
     */
    public function testGetSchema(): void
    {
        $schema = $this->SchemaBehavior->getFilterSchema();

        $this->assertCount(16, $schema);

        foreach($schema as $property) {
            $this->assertArrayHasKey('name', $property);
            $this->assertArrayHasKey('label', $property);
            $this->assertArrayHasKey('options', $property);
            $this->assertArrayHasKey('type', $property['options']);
            $this->assertArrayHasKey('allowEmpty', $property['options']);

            $type = $property['options']['type'];

            $this->assertContains($type, ['string', 'integer', 'double', 'enum', 'boolean', 'date', 'datetime', 'time']);

            if (in_array($type, ['string', 'integer', 'double', 'enum'])) {
                $this->assertArrayHasKey('validation', $property['options']);
            }

            if ('string' === $type) {
                $this->assertArrayHasKey('maxLen', $property['options']['validation']);
                $this->assertArrayHasKey('pattern', $property['options']['validation']);
            }

            if ('integer' === $type || 'double' === $type) {
                $this->assertArrayHasKey('min', $property['options']['validation']);
                $this->assertArrayHasKey('max', $property['options']['validation']);
                $this->assertArrayHasKey('step', $property['options']['validation']);
            }

            if ('enum' === $type) {
                $this->assertArrayHasKey('options', $property['options']['validation']);
            }
        }
    }
}
