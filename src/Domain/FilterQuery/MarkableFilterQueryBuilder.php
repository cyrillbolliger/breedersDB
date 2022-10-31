<?php

declare(strict_types=1);

namespace App\Domain\FilterQuery;

use Cake\Database\Expression\QueryExpression;
use Cake\Datasource\FactoryLocator;
use Cake\Datasource\QueryInterface;

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

        /** @noinspection ProperNullCoalescingOperatorUsageInspection */
        $query = $this->table
            ->find()
            ->distinct($tablePrimaryKey)
            ->contain(self::MARKS_TABLE, $markFilterConditions ?? false)
            ->leftJoinWith(self::MARKS_TABLE)
            ->where(fn(QueryExpression $exp) => $exp->in($tablePrimaryKey, $subQuery));


        if ($this->isVarietyQuery()) {
            // add tree marks
            /** @noinspection ProperNullCoalescingOperatorUsageInspection */
            $query->contain(self::TREES_TABLE . '.' . self::MARKS_TABLE, $markFilterConditions ?? false)
                ->leftJoinWith(self::TREES_TABLE . '.' . self::MARKS_TABLE);
        }

        $this->query = $query;
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

        $matches = [];
        if (!preg_match('/Mark\.(\d+)/', $rule['column']['value'], $matches)) {
            return;
        }

        $rule1 = [
            'column' => ['value' => 'MarksView.property_id'],
            'comparator' => ['value' => '==='],
            'criteria' => (int)$matches[1]
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

    protected function getAllowedTables(): array
    {
        return [$this->baseTable, self::MARKS_TABLE];
    }
}
