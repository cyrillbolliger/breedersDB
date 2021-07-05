<?php
namespace App\Generator;

/**
 * MarkForms generator.
 */
class MarkFormsGenerator
{

    public function generate()
    {
        $date = date('Y-m-d H:i:s');
        $data = [
            [
                'name' => 'Bonitur Mai',
                'description' => 'Die folgenden Werte werden jedes Jahr im Mai erfasst.',
                'created' => $date,
                'modified' => $date,
            ],
            [
                'name' => 'Bonitur August',
                'description' => 'Die folgenden Werte werden jedes Jahr im August erfasst.',
                'created' => $date,
                'modified' => $date,
            ],
            [
                'name' => 'Fruchtwerte',
                'description' => 'Erfassung nach der Ernte',
                'created' => $date,
                'modified' => $date,
            ],
        ];

        return $data;
    }
}
