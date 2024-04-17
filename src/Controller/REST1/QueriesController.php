<?php

declare(strict_types=1);

namespace App\Controller\REST1;

use App\Controller\Component\JsonResponseComponent;
use App\Controller\REST1Controller;
use App\Domain\FilterQuery\FilterQueryBuilder;
use App\Domain\FilterQuery\MarkableFilterQueryBuilder;
use App\Domain\QueryExporter\ExcelExporter;
use App\Domain\QueryExporter\MarkedCollectionDataExtractor;
use App\Domain\QueryExporter\RegularCollectionDataExtractor;
use App\Model\Table\BatchesViewTable;
use App\Model\Table\CrossingsViewTable;
use App\Model\Table\MarksViewTable;
use App\Model\Table\MotherTreesViewTable;
use App\Model\Table\ScionsBundlesViewTable;
use App\Model\Table\TreesViewTable;
use Cake\Core\Configure;
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
    public function isAuthorized($user = null): bool
    {
        return $user['level'] === 0;
    }

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
        foreach ($tablesBaseNames as $tableBaseName) {
            /** @var BatchesViewTable|CrossingsViewTable|MotherTreesViewTable|ScionsBundlesViewTable|TreesViewTable|MarksViewTable $table */
            $table = FactoryLocator::get('Table')->get("{$tableBaseName}View");
            $schemas[$tableBaseName] = $table->getFilterSchema();
        }

        $this->set('data', $schemas);
    }

    /**
     * @throws \JsonException
     */
    public function findResults()
    {
        if (!$this->request->is('post')) {
            return $this->response
                ->withStatus(405)
                ->withAddedHeader('Allow', 'POST');
        }

        $limit = (int)$this->request->getQuery('limit');
        if ($limit <= 0 || $limit > 1000) {
            $limit = 100;
        }

        $queryBuilder = FilterQueryBuilder::create($this->request->getData('data'));
        $queryBuilder->setLimit($limit);
        $queryBuilder->setOffset((int)$this->request->getQuery('offset', 0));
        $queryBuilder->setSortBy($this->request->getQuery('sortBy'));
        $queryBuilder->setOrder($this->request->getQuery('order'));

        try {
            $count = $queryBuilder->getCount();
            $schema = $queryBuilder->getCachedSchema();
            $results = $queryBuilder->getResults();
            $sortBy = $queryBuilder->getSortBy();
            $order = $queryBuilder->getOrder();
            $offset = $queryBuilder->getOffset();
            $limit = $queryBuilder->getLimit();
        } catch (\Exception $e) {
            return $this->JsonResponse->respondWithErrorJson([$e->getMessage()], 422);
        }

        if (!$queryBuilder->isValid()) {
            return $this->JsonResponse->respondWithErrorJson($queryBuilder->getErrors(), 422);
        }

        $this->set('data', [
            'count' => $count,
            'offset' => $offset,
            'sortBy' => $sortBy,
            'order' => $order,
            'limit' => $limit,
            'schema' => $schema,
            'results' => $results?->compile(false),
            'debug' => Configure::read('debug', false)
                ? $queryBuilder->getSql()
                : null
        ]);
    }

    public function download()
    {
        if (!$this->request->is('post')) {
            return $this->response
                ->withStatus(405)
                ->withAddedHeader('Allow', 'POST');
        }

        $queryBuilder = FilterQueryBuilder::create($this->request->getData('data'));
        $queryBuilder->setUserExperimentSiteIds($this->getUserExperimentSiteIds());
        $queryBuilder->setLimit(0);

        try {
            $results = $queryBuilder->getResults();
        } catch (\Exception $e) {
            return $this->JsonResponse->respondWithErrorJson([$e->getMessage()], 422);
        }

        if (!$results || !$queryBuilder->isValid()) {
            return $this->JsonResponse->respondWithErrorJson($queryBuilder->getErrors(), 422);
        }

        if ($queryBuilder instanceof MarkableFilterQueryBuilder) {
            $dataExtractor = new MarkedCollectionDataExtractor(
                $results,
                $this->request->getData('data.columns', []),
                $this->request->getData('data.onlyRowsWithMarks', false),
            );
        } else {
            $dataExtractor = new RegularCollectionDataExtractor(
                $results,
                $this->request->getData('data.columns', []),
            );
        }

        $exporter = new ExcelExporter($dataExtractor);
        $excelFileAsString = $exporter->generate();

        $this->set('data', base64_encode($excelFileAsString));
    }

    public function index()
    {
        if (!$this->request->is('get')) {
            return $this->response
                ->withStatus(405)
                ->withAddedHeader('Allow', 'GET');
        }

        $data = $this->Queries
            ->find('version1')
            ->contain('QueryGroups')
            ->order(['QueryGroups.code', 'Queries.code'])
            ->all();

        $this->set('data', $data);
    }

    public function add()
    {
        if (!$this->request->is('post')) {
            return $this->response
                ->withStatus(405)
                ->withAddedHeader('Allow', 'POST');
        }

        $query = $this->Queries->newEmptyEntity();
        $query = $this->Queries->patchEntity(
            $query,
            $this->request->getData('data')
        );

        if (!$this->Queries->save($query)) {
            return $this->JsonResponse->respondWithErrorJson(
                $query->getErrors(),
                422
            );
        }

        $this->set('data', $this->Queries->get($query->id));
    }

    public function view($id)
    {
        if (!$this->request->is('get')) {
            return $this->response
                ->withStatus(405)
                ->withAddedHeader('Allow', 'GET');
        }

        $query = $this->Queries->get((int)$id);

        $this->set('data', $query);
    }

    public function edit($id)
    {
        if (!$this->request->is('patch')) {
            return $this->response
                ->withStatus(405)
                ->withAddedHeader('Allow', 'PATCH');
        }
        $query = $this->Queries->get((int)$id);
        $query = $this->Queries->patchEntity(
            $query,
            $this->request->getData('data')
        );

        if (!$this->Queries->save($query)) {
            return $this->JsonResponse->respondWithErrorJson(
                $query->getErrors(),
                422
            );
        }

        $this->set('data', $query);
    }

    public function delete($id)
    {
        if (!$this->request->is('delete')) {
            return $this->response
                ->withStatus(405)
                ->withAddedHeader('Allow', 'DELETE');
        }

        $query = $this->Queries->get((int)$id);

        if (!$this->Queries->delete($query)) {
            return $this->JsonResponse->respondWithErrorJson(
                ["Failed to delete query: $id"],
                400
            );
        }

        return $this->response
            ->withStatus(204);
    }

    public function viewByCode($code)
    {
        if (!$this->request->is('get')) {
            return $this->response
                ->withStatus(405)
                ->withAddedHeader('Allow', 'GET');
        }

        $query = $this->Queries
            ->find()
            ->where(['code' => $code])
            ->first();

        if (empty($query)) {
            return $this->response
                ->withStatus(404);
        }

        $this->set('data', $query);
    }

    private function getUserExperimentSiteIds(): array
    {
        $session = $this->request->getSession();
        $experimentSites = $session->read('experiment_sites');

        return is_array($experimentSites) ? $experimentSites : [];
    }
}
