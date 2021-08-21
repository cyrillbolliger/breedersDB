<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\MarkFormProperty;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\MarkFormProperty Test Case
 */
class MarkFormPropertyTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Entity\MarkFormProperty
     */
    protected $MarkFormProperty;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->MarkFormProperty = new MarkFormProperty();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->MarkFormProperty);

        parent::tearDown();
    }

    public function test_getNumberConstraints_integer(): void {
        $this->MarkFormProperty->field_type = 'INTEGER';
        $this->MarkFormProperty->validation_rule = [
            'min' => '0',
            'max' => '10',
            'step' => '1'
        ];

        self::assertSame(0, $this->MarkFormProperty->number_constraints->min);
        self::assertSame(10, $this->MarkFormProperty->number_constraints->max);
        self::assertSame(1, $this->MarkFormProperty->number_constraints->step);
    }

    public function test_getNumberConstraints_float(): void {
        $this->MarkFormProperty->field_type = 'FLOAT';
        $this->MarkFormProperty->validation_rule = [
            'min' => '0',
            'max' => '10',
            'step' => '1'
        ];

        self::assertSame(0.0, $this->MarkFormProperty->number_constraints->min);
        self::assertSame(10.0, $this->MarkFormProperty->number_constraints->max);
        self::assertSame(1.0, $this->MarkFormProperty->number_constraints->step);
    }

    public function test_getNumberConstraints_varchar(): void {
        $this->MarkFormProperty->field_type = 'VARCHAR';
        $this->MarkFormProperty->validation_rule = [ 'min' => '0', 'max' => '10', 'step' => '1' ];

        self::assertNull($this->MarkFormProperty->number_constraints);
    }

    public function test_getNumberConstraints_boolean(): void {
        $this->MarkFormProperty->field_type = 'BOOLEAN';
        $this->MarkFormProperty->validation_rule = [ 'min' => '0', 'max' => '10', 'step' => '1' ];

        self::assertNull($this->MarkFormProperty->number_constraints);
    }

    public function test_getNumberConstraints_date(): void {
        $this->MarkFormProperty->field_type = 'DATE';
        $this->MarkFormProperty->validation_rule = [ 'min' => '0', 'max' => '10', 'step' => '1' ];

        self::assertNull($this->MarkFormProperty->number_constraints);
    }
}
