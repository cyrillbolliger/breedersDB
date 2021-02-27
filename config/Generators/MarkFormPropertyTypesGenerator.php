<?php
namespace App\Generator;

/**
 * MarkFormPropertyTypes generator.
 */
class MarkFormPropertyTypesGenerator
{

    public function generate()
    {
        $data = [
            [
                'name' => 'Bonitur',
            ],
            [
                'name' => 'Behandlung',
            ],
            [
                'name' => 'Hinweis',
            ],
            [
                'name' => 'Muster',
            ],
        ];

        return $data;
    }
}
