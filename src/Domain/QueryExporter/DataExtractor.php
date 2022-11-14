<?php

declare(strict_types=1);

namespace App\Domain\QueryExporter;

use Cake\Collection\CollectionInterface;

interface DataExtractor extends \Iterator
{
    /**
     * @param CollectionInterface $collection
     * @param string[] $columnKeys
     */
    public function __construct(CollectionInterface $collection, array $columnKeys);

    public function getHeaders(): array;

    public function current(): array|null;
}
