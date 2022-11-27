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
        $this->query = $this->table
            ->find()
            ->where('1=1'); // required, else the query built is invalid if no baseFilter is given

        if (!empty($this->rawQuery['baseFilter'])) {
            $this->addWhere();
        }

        $this->setColumns();
    }

    /**
     * @throws FilterQueryException
     */
    protected function addWhere(): void
    {
        $filterQuery = new FilterQueryNode($this->rawQuery['baseFilter']);
        $this->query->andWhere($filterQuery->getConditions([$this->baseTable]));
    }

    private function setColumns(): void
    {
        if (empty($this->rawQuery['columns'])) {
            $this->query->select(['id']);
            return;
        }

        $this->query->select($this->rawQuery['columns']);
    }
}
