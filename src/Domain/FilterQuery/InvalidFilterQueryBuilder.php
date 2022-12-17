<?php

declare(strict_types=1);

namespace App\Domain\FilterQuery;

use Cake\Collection\CollectionInterface;
use Cake\Datasource\QueryInterface;

class InvalidFilterQueryBuilder extends FilterQueryBuilder
{
    protected function buildQuery(): void
    {
    }

    public function getCount(): int|null
    {
        return null;
    }

    public function getResults(): CollectionInterface|null
    {
        return null;
    }

    protected function getQuery(): QueryInterface|null
    {
        return null;
    }

    protected function getSchema(): array|null
    {
        return null;
    }
}
