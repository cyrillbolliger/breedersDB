<?php

declare(strict_types=1);

namespace App\Controller\REST1;

use App\Controller\Component\JsonResponseComponent;
use App\Controller\REST1Controller;
use App\Domain\FilterQuery\FilterQueryBuilder;
use App\Model\Table\BatchesViewTable;
use App\Model\Table\CrossingsViewTable;
use App\Model\Table\MarksViewTable;
use App\Model\Table\MotherTreesViewTable;
use App\Model\Table\ScionsBundlesViewTable;
use App\Model\Table\TreesViewTable;
use Cake\Datasource\FactoryLocator;

/**
 * Queries Controller
 *
 * @property \App\Model\Table\QueriesTable $Queries
 * @property JsonResponseComponent $JsonResponse
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
            /** @var BatchesViewTable|CrossingsViewTable|MotherTreesViewTable|ScionsBundlesViewTable|TreesViewTable|MarksViewTable $table */
            $table = FactoryLocator::get('Table')->get( "{$tableBaseName}View" );
            $schemas[$tableBaseName] = $table->getFilterSchema();
        }

        $this->set('data', $schemas);
    }

    public function findResults() {
        if (!$this->request->is('post')) {
            return $this->response
                ->withStatus(405)
                ->withAddedHeader('Allow', 'POST');
        }

        $queryBuilder = FilterQueryBuilder::create($this->request->getData('data'));
        $query = $queryBuilder->getQuery();

        if (!$query || !$queryBuilder->isValid()) {
            return $this->JsonResponse->respondWithErrorJson($queryBuilder->getErrors(), 422);
        }

        $this->set('data', $query->all());
    }
}
