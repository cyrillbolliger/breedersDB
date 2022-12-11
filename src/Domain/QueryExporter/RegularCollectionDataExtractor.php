<?php

declare(strict_types=1);

namespace App\Domain\QueryExporter;


class RegularCollectionDataExtractor extends DataExtractor
{
    public function current(): array|null
    {
        if (!$this->collection->valid()) {
            return null;
        }

        $row = [];
        foreach ($this->getEntityFieldNames() as $name) {
            $row[$name] = $this->collection->current()->$name;
        }

        return $row;
    }

    public function valid(): bool
    {
        return $this->collection->valid();
    }

    public function next(): void
    {
        $this->collection->next();
    }

    public function key(): mixed
    {
        return $this->collection->key();
    }

    public function rewind(): void
    {
        $this->collection->rewind();
    }
}
