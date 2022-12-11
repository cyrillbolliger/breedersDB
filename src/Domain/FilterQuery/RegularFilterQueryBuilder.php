<?php

declare(strict_types=1);

namespace App\Domain\FilterQuery;

use Cake\Collection\CollectionInterface;
use Cake\Datasource\QueryInterface;

class RegularFilterQueryBuilder extends FilterQueryBuilder
{
    public function getCount(): int|null
    {
        return $this->getQuery()?->count();
    }

    protected function getQuery(): QueryInterface|null
    {
        if (!isset($this->query)) {
            if (!$this->isValid()) {
                return null;
            }

            try {
                $this->buildQuery();
            } catch (FilterQueryException $e) {
                $this->addError($e->getMessage());
                return null;
            }

            if ($this->getLimit()) {
                $this->query->limit($this->getLimit());
                $this->query->offset($this->getOffset());
            }

            $this->query->order([$this->getSortBy() => $this->getOrder()]);
        }

        return $this->query;
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

    public function getResults(): CollectionInterface|null
    {
        return $this->getQuery()?->all();
    }

    protected function getSchema(): array
    {
        return [$this->baseTable => $this->getFilterSchema($this->table)];
    }
}
