<?php
namespace App\Generator;


use App\Domain\Settings;

/**
 * Settings generator.
 */
class SettingsGenerator
{

    public function generate()
    {
        return [
            [
                'id' => 1,
                'setting_key' => Settings::ZPL_DRIVER_OFFSET_LEFT,
                'setting_value' => '0',
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
        ];
    }
}
