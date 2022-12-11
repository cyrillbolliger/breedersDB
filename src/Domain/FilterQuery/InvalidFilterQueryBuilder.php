<?php

declare(strict_types=1);

namespace App\Domain\FilterQuery;

class InvalidFilterQueryBuilder extends FilterQueryBuilder
{
    protected function buildQuery(): void
    {
    }

    protected function getAllowedTables(): array
    {
        return [];
    }
}
