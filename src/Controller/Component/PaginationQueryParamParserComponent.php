<?php

declare(strict_types=1);

namespace App\Controller\Component;

use Cake\Controller\Component;

/**
 * PaginationParamParser component
 */
class PaginationQueryParamParserComponent extends Component
{
    public function getLimit(): ?int
    {
        $limit = (int)$this->getController()->getRequest()->getQuery('limit');
        if ($limit < 1) {
            return null;
        }
        return $limit;
    }

    public function getOffset(): ?int
    {
        $offset = (int)$this->getController()->getRequest()->getQuery('offset');
        if ($offset < 1) {
            return null;
        }
        return $offset;
    }

    /**
     * @throws \Exception
     */
    public function getOrder(): array
    {
        $request = $this->getController()->getRequest();

        $sortBy = $request->getQuery('sortBy') ?? '';
        $order = strtolower($request->getQuery('order') ?? '') === 'desc'
            ? 'desc'
            : 'asc';

        $innocuousColumnName = preg_match('/^[0-9a-zA-Z$_.]*$/', $sortBy ?? '');
        if (!$innocuousColumnName) {
            throw new \Exception("Invalid query param `sortBy`: $sortBy");
        }

        if (!$sortBy) {
            return [];
        }

        return [$sortBy => $order];
    }
}
