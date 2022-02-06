<?php

namespace App\Test\TestCase\Domain;

use App\Domain\Settings;
use Cake\TestSuite\TestCase;

class SettingsTest extends TestCase
{
    public function testSetZplDriverOffsetLeft()
    {
        Settings::setZplDriverOffsetLeft(123);
        self::assertEquals(123, Settings::getZplDriverOffsetLeft());
    }

    public function testGetZplDriverOffsetLeft()
    {
        Settings::setZplDriverOffsetLeft(555);
        self::assertEquals(555, Settings::getZplDriverOffsetLeft());
    }

    public function testSetZplDriverOffsetLeft__update()
    {
        Settings::setZplDriverOffsetLeft(666);
        self::assertEquals(666, Settings::getZplDriverOffsetLeft());

        Settings::setZplDriverOffsetLeft(777);
        self::assertEquals(777, Settings::getZplDriverOffsetLeft());
    }
}
