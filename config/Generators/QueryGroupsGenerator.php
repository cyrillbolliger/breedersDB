<?php
namespace App\Generator;

/**
 * QueryGroups generator.
 */
class QueryGroupsGenerator
{

    public function generate()
    {
        $data = [
            [
                'id' => '1',
                'code' => 'Bonituren',
                'deleted' => NULL,
                'created' => '2020-11-29 22:44:02',
                'modified' => '2020-11-29 22:44:02',
            ],
            [
                'id' => '2',
                'code' => 'Pflanzen',
                'deleted' => NULL,
                'created' => '2020-11-29 22:57:15',
                'modified' => '2020-11-29 23:13:57',
            ],
        ];

        return $data;
    }
}
