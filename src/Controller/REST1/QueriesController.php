<?php

declare(strict_types=1);

namespace App\Controller\REST1;

use App\Controller\REST1Controller;
use Cake\Datasource\FactoryLocator;

/**
 * Queries Controller
 *
 * @property \App\Model\Table\QueriesTable $Queries
 * @method \App\Model\Entity\Query[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class QueriesController extends REST1Controller
{
    public function getFilterSchemas(): void
    {
        $tablesBaseNames = [
            'Batches',
            'Crossings',
            'MotherTrees',
            'ScionsBundles',
            'Trees',
            'Varieties',
            'Marks'
        ];

        $schemas = [];
        foreach( $tablesBaseNames as $tableBaseName ) {
            $table = FactoryLocator::get('Table')->get( "{$tableBaseName}View" );
            $schemas[$tableBaseName] = $table->getFilterSchema();
        }

        $this->set('data', $schemas);
    }
}
