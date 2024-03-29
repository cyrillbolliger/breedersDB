<?php

declare(strict_types=1);

namespace App\Domain\FilterQuery;

use Cake\Cache\Cache;
use Cake\Collection\CollectionInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Datasource\QueryInterface;
use Cake\Datasource\RepositoryInterface;

abstract class FilterQueryBuilder
{
    protected const ALLOWED_BASE_TABLES = [
        'BatchesView',
        'CrossingsView',
        'MotherTreesView',
        'ScionsBundlesView',
        'TreesView',
        'VarietiesView',
    ];

    private const MARKABLE_TABLES = [
        'BatchesView',
        'VarietiesView',
        'TreesView',
    ];

    private const FILTER_SCHEMA_CACHE_KEY = 'FilterQueryBuilderFilterSchema';

    protected string $baseTable;
    protected QueryInterface $query;
    protected RepositoryInterface $table;
    /**
     * @var string[]
     */
    private array $errors = [];

    private string $order;
    private int $limit;
    private int $offset;
    private string $sortBy;

    protected function __construct(
        protected readonly array $rawQuery,
    ) {
        $this->setBaseTable();
        try {
            $this->setTable();
        } catch (FilterQueryException $e) {
            $this->addError($e->getMessage());
        }
    }

    private function setBaseTable(): void
    {
        if (!isset($this->rawQuery['baseTable'])) {
            $this->addError('Missing base table.');
            return;
        }

        $baseTable = $this->rawQuery['baseTable'];

        if (!in_array("{$baseTable}View", self::ALLOWED_BASE_TABLES, true)) {
            $this->addError('Invalid base table: ' . $baseTable);
            return;
        }

        $this->baseTable = "{$baseTable}View";
    }

    protected function addError(string $message): void
    {
        $this->errors[] = $message;
    }

    /**
     * @throws FilterQueryException
     */
    protected function setTable(): void
    {
        if (!in_array($this->baseTable, self::ALLOWED_BASE_TABLES, true)) {
            throw new FilterQueryException('Invalid base table: ' . $this->baseTable);
        }

        $this->table = FactoryLocator::get('Table')
            ->get($this->baseTable);
    }

    public static function create(array $rawQuery): FilterQueryBuilder
    {
        if (!isset($rawQuery['baseTable'])) {
            return new InvalidFilterQueryBuilder($rawQuery);
        }

        if (in_array("{$rawQuery['baseTable']}View", self::MARKABLE_TABLES, true)) {
            return new MarkableFilterQueryBuilder($rawQuery);
        }

        return new RegularFilterQueryBuilder($rawQuery);
    }

    abstract public function getCount(): int|null;

    public function isValid(): bool
    {
        return empty($this->errors);
    }

    public function getLimit(): int
    {
        if (!isset($this->limit)) {
            $this->setLimit();
        }

        return $this->limit;
    }

    public function setLimit(int $limit = 100): void
    {
        $this->limit = $limit;
    }

    public function getSortBy(): string
    {
        if (!isset($this->sortBy)) {
            $this->setSortBy(null);
        }

        return $this->sortBy;
    }

    public function setSortBy(string|null $sortBy): void
    {
        $innocuousColumnName = preg_match('/^[0-9a-zA-Z$_.]+$/', $sortBy ?? '');

        if (empty($sortBy) || !$innocuousColumnName) {
            $sortBy = "{$this->baseTable}.id";
        }

        $this->sortBy = $sortBy;
    }

    public function getOrder(): string
    {
        if (!isset($this->order)) {
            $this->setOrder(null);
        }

        return $this->order;
    }

    public function setOrder(string|null $order): void
    {
        $this->order = 'desc' === strtolower($order ?? '') ? 'desc' : 'asc';
    }

    abstract public function getResults(): CollectionInterface|null;

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getSql(): array
    {
        return [
            'sql' => $this->getQuery()?->sql(),
            'params' => $this->getQuery()?->getValueBinder()->bindings(),
        ];
    }

    abstract protected function getQuery(): QueryInterface|null;

    public function getCachedSchema(): array|null
    {
        $cacheKey = self::FILTER_SCHEMA_CACHE_KEY . '-' . $this->baseTable;

        if (0 < $this->getOffset()) {
            $schema = Cache::read($cacheKey);
        }

        if (!empty($schema)) {
            return $schema;
        }

        $schema = $this->getSchema();
        Cache::add($cacheKey, $schema);

        return $schema;
    }

    public function getOffset(): int
    {
        if (!isset($this->offset)) {
            $this->setOffset();
        }

        return $this->offset;
    }

    public function setOffset(int $offset = 0): void
    {
        $this->offset = $offset;
    }

    abstract protected function getSchema(): array|null;

    /**
     * @throws FilterQueryException
     */
    abstract protected function buildQuery(): void;

    protected function getFilterSchema(RepositoryInterface $table): array|null
    {
        if (is_callable([$table, 'getFilterSchema'])) {
            return $table->getFilterSchema();
        }

        $this->addError('No filter schema available for table: ' . $table->getAlias());
        return null;
    }
}
