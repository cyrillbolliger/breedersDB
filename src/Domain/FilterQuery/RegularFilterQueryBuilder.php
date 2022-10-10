<?php

declare(strict_types=1);

namespace App\Domain\FilterQuery;

class RegularFilterQueryBuilder extends FilterQueryBuilder
{
    protected function getSchema(): array
    {
        return [$this->baseTable => $this->getFilterSchema($this->table)];
    }

    /**
     * @throws FilterQueryException
     */
    protected function buildQuery(): void
    {
        $this->query = $this->table->find();

        if (!empty($this->rawQuery['baseFilter'])) {
            $this->addWhere();
        }
    }

    /**
     * @throws FilterQueryException
     */
    protected function addWhere(): void
    {
        $filterQuery = new FilterQueryNode($this->rawQuery['baseFilter']);
        $this->query->where($filterQuery->getConditions([$this->baseTable]));
    }
}
