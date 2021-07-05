<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use App\Generator\QueryGroupsGenerator;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * QueryGroupsFixture
 */
class QueryGroupsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $generator = new QueryGroupsGenerator();
        $this->records = $generator->generate(2);
        parent::init();
    }
}
