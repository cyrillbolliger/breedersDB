<?php

namespace App\Utility;

use Cake\Core\Configure;

class DateTimeHandler
{
    /**
     * Standardizes dates into Y-m-d format. Accepts d.m.Y, d/m/Y and
     * any english date format, that is also accepted by strtotime().
     *
     * @param string $date
     * @return string|null
     */
    public static function parseDateToYmdString(string $date): ?string
    {
        $date = trim($date);

        $converted = false;

        if (Configure::read('localizedDate')) {
            if (self::isGermanDateFormat($date, true)) {
                $converted = date_create_from_format('d.m.Y', $date);
            } elseif (self::isGermanDateFormat($date, false)) {
                $converted = date_create_from_format('d.m.y', $date);
            } elseif (self::isFrenchDateFormat($date, true)) {
                $converted = date_create_from_format('d/m/Y', $date);
            } elseif (self::isFrenchDateFormat($date, false)) {
                $converted = date_create_from_format('d/m/y', $date);
            }
        }

        if (!$converted) {
            $converted = date_create($date);
        }

        if ($converted) {
            return $converted->format('Y-m-d');
        }

        return null;
    }

    private static function isGermanDateFormat(string $date, bool $fullYear = true): bool
    {
        $yearLen = $fullYear ? 4 : 2;
        return (bool)preg_match("/^([0-2]?\d|3[01])\.(0?\d|1[0-2])\.\d{{$yearLen}}$/", $date);
    }

    private static function isFrenchDateFormat(string $date, bool $fullYear = true): bool
    {
        $yearLen = $fullYear ? 4 : 2;
        return (bool)preg_match("/^([0-2]?\d|3[01])\/(0?\d|1[0-2])\/\d{{$yearLen}}$/", $date);
    }
}
