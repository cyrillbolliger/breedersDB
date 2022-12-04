<?php

declare(strict_types=1);

namespace App\Controller\REST1;


use App\Controller\Component\JsonResponseComponent;
use App\Controller\REST1Controller;
use App\Model\Table\QueryGroupsTable;

/**
 * Query Groups Controller
 *
 * @property QueryGroupsTable $QueryGroups
 * @property JsonResponseComponent $JsonResponse
 */
class QueryGroupsController extends REST1Controller
{
    public function index()
    {
        if (!$this->request->is('get')) {
            return $this->response
                ->withStatus(405)
                ->withAddedHeader('Allow', 'GET');
        }

        $data = $this->QueryGroups
            ->find('version1')
            ->order('code')
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

        $queryGroup = $this->QueryGroups->newEmptyEntity();
        $queryGroup = $this->QueryGroups->patchEntity(
            $queryGroup,
            $this->request->getData('data')
        );

        $queryGroup->version = '1.0';

        if (!$this->QueryGroups->save($queryGroup)) {
            return $this->JsonResponse->respondWithErrorJson(
                $queryGroup->getErrors(),
                422
            );
        }

        $this->set('data', $queryGroup);
    }

    public function view($id)
    {
        if (!$this->request->is('get')) {
            return $this->response
                ->withStatus(405)
                ->withAddedHeader('Allow', 'GET');
        }

        $queryGroup = $this->QueryGroups->get((int)$id);

        $this->set('data', $queryGroup);
    }

    public function edit($id)
    {
        if (!$this->request->is('patch')) {
            return $this->response
                ->withStatus(405)
                ->withAddedHeader('Allow', 'PATCH');
        }
        $queryGroup = $this->QueryGroups->get((int)$id);
        $queryGroup = $this->QueryGroups->patchEntity(
            $queryGroup,
            $this->request->getData('data')
        );

        $queryGroup->version = '1.0';

        if (!$this->QueryGroups->save($queryGroup)) {
            return $this->JsonResponse->respondWithErrorJson(
                $queryGroup->getErrors(),
                422
            );
        }

        $this->set('data', $queryGroup);
    }

    public function delete($id)
    {
        if (!$this->request->is('delete')) {
            return $this->response
                ->withStatus(405)
                ->withAddedHeader('Allow', 'DELETE');
        }

        $queryGroup = $this->QueryGroups->get((int)$id);

        if (!$this->QueryGroups->delete($queryGroup)) {
            return $this->JsonResponse->respondWithErrorJson(
                ["Failed to delete query group: $id"],
                400
            );
        }

        return $this->response
            ->withStatus(204);
    }
}
