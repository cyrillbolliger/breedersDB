<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use App\Generator\UsersGenerator;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 */
class UsersFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $generator = new UsersGenerator();
        $this->records = $generator->generate(5);
        parent::init();
    }
}
