<?php

declare(strict_types=1);

namespace App\Domain\QueryExporter;

use Cake\Collection\CollectionInterface;
use Cake\Datasource\FactoryLocator;

class RegularCollectionDataExtractor implements DataExtractor
{
    /**
     * The field names of the entity
     *
     * @var string[]
     */
    private array $entityFieldNames;

    public function __construct(
        private readonly CollectionInterface $collection,
        private readonly array $columnKeys,
    ) {
    }

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

    private function getEntityFieldNames(): array
    {
        if (!isset($this->entityFieldNames)) {
            $this->entityFieldNames = array_map(
                static fn($col) => substr($col, strpos($col, '.') + 1),
                $this->columnKeys,
            );
        }

        return $this->entityFieldNames;
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

    public function getHeaders(): array
    {
        $tables = [];
        $tableLocator = FactoryLocator::get('Table');

        $headers = [];
        foreach ($this->columnKeys as $key) {
            [$tableName, $columnName] = explode('.', $key);

            if (!isset($tables[$tableName])) {
                $tables[$tableName] = $tableLocator->get($tableName);
            }

            $table = $tables[$tableName];

            if (method_exists($table, 'getTranslatedColumnName')
                && method_exists($table, 'getTranslatedName')
            ) {
                $header = $table->getTranslatedName() . ' > ' . $table->getTranslatedColumnName($columnName);
            }

            $headers[] = $header ?? $key;
        }

        return $headers;
    }
}
