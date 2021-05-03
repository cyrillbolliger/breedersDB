<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\SettingsController;
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
