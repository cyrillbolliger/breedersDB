<?php

namespace App\Test\TestCase\Utility;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use App\Utility\DateTimeHandler;

class DateTimeHandlerTest extends TestCase
{

    public function testParseDateToYmdString_de(): void
    {
        Configure::write('localizedDate', true);

        self::assertEquals(
            '2021-09-01',
            DateTimeHandler::parseDateToYmdString('1.9.21')
        );
        self::assertEquals(
            '2021-09-01',
            DateTimeHandler::parseDateToYmdString('01.09.21')
        );
        self::assertEquals(
            '2021-09-01',
            DateTimeHandler::parseDateToYmdString('1.9.2021')
        );
        self::assertEquals(
            '2021-09-01',
            DateTimeHandler::parseDateToYmdString('01.09.2021')
        );
    }

    public function testParseDateToYmdString_fr(): void
    {
        Configure::write('localizedDate', true);

        self::assertEquals(
            '2021-09-01',
            DateTimeHandler::parseDateToYmdString('1/9/21')
        );
        self::assertEquals(
            '2021-09-01',
            DateTimeHandler::parseDateToYmdString('01/09/21')
        );
        self::assertEquals(
            '2021-09-01',
            DateTimeHandler::parseDateToYmdString('1/9/2021')
        );
        self::assertEquals(
            '2021-09-01',
            DateTimeHandler::parseDateToYmdString('01/09/2021')
        );
    }

    public function testParseDateToYmdString_en(): void
    {
        self::assertEquals(
            '2021-09-01',
            DateTimeHandler::parseDateToYmdString('2021-09-01T00:00:00.000Z')
        );
        self::assertEquals(
            '2021-09-01',
            DateTimeHandler::parseDateToYmdString('2021-09-01T00:00:00+00:00')
        );
        self::assertEquals(
            '2021-09-01',
            DateTimeHandler::parseDateToYmdString('2021-09-01T00:00:00')
        );
        self::assertEquals(
            '2021-09-01',
            DateTimeHandler::parseDateToYmdString('2021-09-01 00:00:00')
        );
        self::assertEquals(
            '2021-09-01',
            DateTimeHandler::parseDateToYmdString('2021-09-01')
        );
    }
}
