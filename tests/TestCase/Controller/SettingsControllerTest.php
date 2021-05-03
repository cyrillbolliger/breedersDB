<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\SettingsController;
use App\Test\Util\AuthenticateTrait;
use App\Test\Util\DependsOnFixtureTrait;
use App\Test\Util\ExperimentSiteTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\SettingsController Test Case
 *
 * @uses \App\Controller\SettingsController
 */
class SettingsControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use AuthenticateTrait;
    use ExperimentSiteTrait;
    use DependsOnFixtureTrait;

    protected array $dependsOnFixture = [];

    protected function setUp(): void {
        $this->authenticate();
        $this->setSite();
        parent::setUp();
    }

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndex(): void
    {
        $this->get( '/settings' );

        $this->assertResponseSuccess();
        $this->assertResponseCode( 200 );
    }
}
