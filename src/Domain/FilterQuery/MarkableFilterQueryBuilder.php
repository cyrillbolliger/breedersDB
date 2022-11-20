<?php

declare(strict_types=1);

namespace App\Domain\FilterQuery;

use Cake\Database\Expression\QueryExpression;
use Cake\Datasource\FactoryLocator;
use Cake\ORM\Query;

class MarkableFilterQueryBuilder extends FilterQueryBuilder
{
    private const MARKS_TABLE = 'MarksView';
    private const VARIETIES_TABLE = 'VarietiesView';
    private const TREES_TABLE = 'TreesView';

    protected function getSchema(): array|null
    {
        $schemas = [
            $this->baseTable => $this->getFilterSchema($this->table),
            'MarksView' => $this->getFilterSchema(FactoryLocator::get('Table')->get('MarksView')),
        ];

        if ($this->isVarietyQuery()) {
            $schemas['TreesView'] = $this->getFilterSchema(FactoryLocator::get('Table')->get('TreesView'));
        }

        return $schemas;
    }

    private function isVarietyQuery(): bool
    {
        return $this->baseTable === self::VARIETIES_TABLE;
    }

    protected function buildQuery(): void
    {
        $tablePrimaryKey = "{$this->baseTable}.id";

        $subQueryPrimaryKey = '_id';
        $subQueryTableAlias = '_SubQuery';

        $baseSubQuery = $this->table
            ->find()
            ->select([$subQueryPrimaryKey => $tablePrimaryKey])
            ->distinct($subQueryPrimaryKey)
            ->where('1=1'); // required, else the query built is invalid if no baseFilter is given

        if (!empty($this->rawQuery['baseFilter'])) {
            $baseSubQuery->andWhere($this->getFilterConditions($this->rawQuery['baseFilter']));
        }

        if ($this->isVarietyQuery()) {
            // consider variety marks AND at tree marks
            $varietiesWithTreeMarks = clone $baseSubQuery;
            $varietiesWithTreeMarks->leftJoinWith(self::TREES_TABLE . '.' . self::MARKS_TABLE);
            $varietiesWithVarietyMarks = $baseSubQuery->leftJoinWith(self::MARKS_TABLE);
            $union = $varietiesWithVarietyMarks->union($varietiesWithTreeMarks);

            // wrap union into a select statement, else we can't use it as subquery
            $subQuery = $this->table
                ->find()
                ->from([$subQueryTableAlias => $union])
                ->select(["$subQueryTableAlias.$subQueryPrimaryKey"])
                ->distinct("$subQueryTableAlias.$subQueryPrimaryKey");
        } else {
            $subQuery = $baseSubQuery->leftJoinWith(self::MARKS_TABLE);
        }


        if (!empty($this->rawQuery['markFilter'])) {
            $markFilterConditions = $this->getFilterConditions($this->rawQuery['markFilter']);
        }

        if (!empty($this->rawQuery['columns'])) {
            /** @noinspection ProperNullCoalescingOperatorUsageInspection */
            $columnLimitedMarkFilterConditions = $this->getColumnLimitedMarkFilterConditions(
                $this->rawQuery['columns'],
                $markFilterConditions ?? false
            );
        }

        $containCondition = $columnLimitedMarkFilterConditions ?? $markFilterConditions ?? false;

        $query = $this->table
            ->find()
            ->distinct($tablePrimaryKey)
            ->contain(self::MARKS_TABLE, $containCondition)
            ->leftJoinWith(self::MARKS_TABLE, $containCondition)
            ->where(fn(QueryExpression $exp) => $exp->in($tablePrimaryKey, $subQuery));


        if ($this->isVarietyQuery()) {
            // add tree marks
            $query->contain(self::TREES_TABLE . '.' . self::MARKS_TABLE, $containCondition)
                ->leftJoinWith(self::TREES_TABLE . '.' . self::MARKS_TABLE);
        }

        $this->query = $query;

        if (!empty($this->rawQuery['columns'])) {
            $this->setColumns();
        }
    }

    /**
     * @throws FilterQueryException
     */
    protected function getFilterConditions(array $rawFilter): callable
    {
        $filterData = $this->transformMarkCriteriaRecursive($rawFilter);
        return (new FilterQueryNode($filterData))
            ->getConditions($this->getAllowedTables());
    }

    private function transformMarkCriteriaRecursive(array $filter): array
    {
        if (!empty($filter['children'])) {
            foreach ($filter['children'] as $idx => $child) {
                $filter['children'][$idx] = $this->transformMarkCriteriaRecursive($child);
            }
        }

        if (!empty($filter['filterRule'])) {
            $this->transformSingleCriteria($filter);
        }

        return $filter;
    }

    private function transformSingleCriteria(array &$filterNode): void
    {
        if (empty($filterNode['filterRule'])) {
            return;
        }

        $rule = $filterNode['filterRule'];

        if (empty($rule['column']['value'])) {
            return;
        }

        $markPropertyId = $this->parseMarkPropertyId($rule['column']['value']);
        if (!$markPropertyId) {
            return;
        }

        $rule1 = [
            'column' => ['value' => 'MarksView.property_id'],
            'comparator' => ['value' => '==='],
            'criteria' => $markPropertyId
        ];
        $rule2 = [
            'column' => ['value' => 'MarksView.value'],
            'comparator' => ['value' => $rule['comparator']['value'] ?? null],
            'criteria' => $rule['criteria'] ?? null
        ];

        $node1 = [
            'filterRule' => $rule1,
            'children' => [],
            'childrensOperand' => null
        ];
        $node2 = [
            'filterRule' => $rule2,
            'children' => [],
            'childrensOperand' => null
        ];

        $filterNode['filterRule'] = null;
        $filterNode['childrensOperand'] = 'and';
        $filterNode['children'] = [$node1, $node2];
    }

    private function parseMarkPropertyId(string $columnName): int|null
    {
        $matches = [];
        if (!preg_match('/Mark\.(\d+)/', $columnName, $matches)) {
            return null;
        }
        return (int)$matches[1];
    }

    protected function getAllowedTables(): array
    {
        return [$this->baseTable, self::MARKS_TABLE];
    }

    private function getColumnLimitedMarkFilterConditions(array $columns, callable|bool $markFilterConditions): callable
    {
        $conditionExp = function (QueryExpression $exp) use ($columns, $markFilterConditions): QueryExpression {
            $propertyIds = [];
            foreach ($columns as $column) {
                $propertyId = $this->parseMarkPropertyId($column);
                if ($propertyId) {
                    $propertyIds[$propertyId] = $propertyId;
                }
            }

            // join only marks with the given property ids
            $markPropertyCondition = $exp->in('MarksView.property_id', $propertyIds);

            if ($markFilterConditions) {
                return $exp->and([$markFilterConditions, $markPropertyCondition]);
            }

            return $markPropertyCondition;
        };


        return static function (QueryExpression|Query $exp) use ($conditionExp) {
            if ($exp instanceof Query) {
                return $exp->andWhere(
                    fn(QueryExpression $queryExp) => $conditionExp($queryExp)
                );
            }

            return $conditionExp($exp);
        };
    }

    private function setColumns(): void
    {
        $frontendColumns = $this->rawQuery['columns'];

        $sqlColumns = [];
        foreach ($frontendColumns as $column) {
            $markPropertyId = $this->parseMarkPropertyId($column);
            if (!$markPropertyId) {
                // only add regular columns. The marks are limited by the contain expression
                $sqlColumns[] = $column;
            }
        }

        // the ORM requires the primary key
        $primaryKey = "{$this->baseTable}.id";
        if (!in_array($primaryKey, $sqlColumns, true)) {
            $sqlColumns[] = $primaryKey;
        }

        $this->query->select($sqlColumns);
    }
}
