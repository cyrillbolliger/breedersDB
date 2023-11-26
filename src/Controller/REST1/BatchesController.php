<?php

declare(strict_types=1);

namespace App\Controller\REST1;

use App\Controller\Component\JsonResponseComponent;
use App\Controller\Component\PaginationQueryParamParserComponent;
use App\Controller\REST1Controller;

/**
 * Batches Controller
 *
 * @property \App\Model\Table\BatchesTable $Batches
 * @property JsonResponseComponent $JsonResponse
 * @property PaginationQueryParamParserComponent $PaginationQueryParamParser
 * @method \App\Model\Entity\Variety[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class BatchesController extends REST1Controller
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('PaginationQueryParamParser');
    }

    public function index()
    {
        if (!$this->request->is('get')) {
            return $this->response
                ->withStatus(405)
                ->withAddedHeader('Allow', 'GET');
        }

        $term = $this->request->getQuery('term');
        $limit = $this->PaginationQueryParamParser->getLimit();
        $offset = $this->PaginationQueryParamParser->getOffset();
        try {
            $order = $this->PaginationQueryParamParser->getOrder();
        } catch (\Exception $e) {
            $this->JsonResponse->respondWithErrorJson([$e->getMessage()], 422);
        }

        $query = !empty($term)
            ? $this->Batches->filterCrossingBatches($term)?->contain(['Crossings'])
            : $this->Batches->find()->contain(['Crossings']);

        if ($query) {
            $dataQuery = $query->limit($limit)->offset($offset)->order($order);
            $count = (clone $dataQuery)->count();
        }

        $this->set(['data' => [
            'count' => $count ?? 0,
            'offset' => $offset ?? 0,
            'sortBy' => !empty($order) ? array_keys($order)[0] : null,
            'order' => !empty($order) ? array_values($order)[0] : null,
            'limit' => $limit,
            'results' => $dataQuery ?? [],
        ]]);
    }
}
