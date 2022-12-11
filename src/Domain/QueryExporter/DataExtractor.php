<?php

declare(strict_types=1);

namespace App\Domain\QueryExporter;

use Cake\Collection\CollectionInterface;
use Cake\Datasource\FactoryLocator;

abstract class DataExtractor implements \Iterator
{
    /**
     * The field names of the entity
     *
     * @var string[]
     */
    private array $entityFieldNames;

    /**
     * @param CollectionInterface $collection
     * @param string[] $columnKeys
     */
    public function __construct(
        protected readonly CollectionInterface $collection,
        protected readonly array $columnKeys,
    ) {
    }

    public function getHeaders(): array
    {
        $tables = [];
        $tableLocator = FactoryLocator::get('Table');

        $headers = [];
        foreach ($this->columnKeys as $key) {
            [$tableName, $columnName] = explode('.', $key);

            if ('Mark' === $tableName) {
                continue;
            }

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

    abstract public function current(): array|null;

    protected function getEntityFieldNames(): array
    {
        if (!isset($this->entityFieldNames)) {
            $columnKeys = array_filter(
                $this->columnKeys,
                static fn(string $key) => !str_starts_with($key, 'Mark.')
            );
            $this->entityFieldNames = array_map(
                static fn($col) => substr($col, strpos($col, '.') + 1),
                $columnKeys,
            );
        }

        return $this->entityFieldNames;
    }
}
