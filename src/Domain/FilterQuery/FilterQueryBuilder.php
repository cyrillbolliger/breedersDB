<?php

declare(strict_types=1);

namespace App\Domain\FilterQuery;

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
    protected string $baseTable;
    protected QueryInterface $query;
    protected RepositoryInterface $table;
    /**
     * @var string[]
     */
    private array $errors = [];

    protected function __construct(
        protected readonly array $rawQuery,
    ) {
        $this->setBaseTable();
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

    public function getCount(): int|null
    {
        return $this->getQuery()?->count();
    }

    public function getQuery(): QueryInterface|null
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
        }

        return $this->query;
    }

    public function isValid(): bool
    {
        return empty($this->errors);
    }

    /**
     * @throws FilterQueryException
     */
    abstract protected function buildQuery(): void;

    public function getResults(): CollectionInterface|null
    {
        return $this->getQuery()?->all();
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    abstract public function getSchema(): array|null;

    public function getSql(): array
    {
        return [
            'sql' => $this->getQuery()?->sql(),
            'params' => $this->getQuery()?->getValueBinder()->bindings(),
        ];
    }

    protected function getFilterSchema(RepositoryInterface $table): array|null
    {
        if (is_callable([$table, 'getFilterSchema'])) {
            return $table->getFilterSchema();
        }

        $this->addError('No filter schema available for table: ' . $table->getAlias());
        return null;
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
}
