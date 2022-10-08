<?php

declare(strict_types=1);

namespace App\Domain\FilterQuery;

use Cake\Database\Expression\QueryExpression;
use Cake\Datasource\QueryInterface;

class MarkableFilterQueryBuilder extends FilterQueryBuilder
{
    private const MARKS_TABLE = 'MarksView';

    protected function buildQuery(): void
    {
        $this->setTable();
        $tablePrimaryKey = "{$this->baseTable}.id";

        $subQuery = $this->table
            ->find()
            ->select([$tablePrimaryKey])
            ->distinct($tablePrimaryKey)
            ->leftJoinWith(self::MARKS_TABLE)
            ->where('1=1'); // required, else the query built is invalid of no baseFilter is given

        if (!empty($this->rawQuery['baseFilter'])) {
            $subQuery = $this->addWhere($subQuery, $this->rawQuery['baseFilter']);
        }

        $query = $this->table
            ->find()
            ->leftJoinWith(self::MARKS_TABLE)
            ->contain(self::MARKS_TABLE)
            ->where(
                fn(QueryExpression $exp) =>
                $exp->in($tablePrimaryKey, $subQuery)
            );

        if (!empty($this->rawQuery['markFilter'])) {
            $query = $this->addWhere($query, $this->rawQuery['markFilter']);
        }

        $this->query = $query;
    }

    /**
     * @throws FilterQueryException
     */
    protected function addWhere(QueryInterface $query, array $rawFilter): QueryInterface
    {
        $filterData = $this->transformMarkCriteriaRecursive($rawFilter);
        $filterQuery = new FilterQueryNode($filterData);
        return $query->andWhere($filterQuery->getConditions($this->getAllowedTables()));
    }

    private function transformMarkCriteriaRecursive(array $filter): array
    {
        if (!empty($filter['children'])) {
            foreach($filter['children'] as $idx => $child) {
                $filter['children'][$idx] = $this->transformMarkCriteriaRecursive($child);
            }
        }

        if (!empty($filter['filterRule'])) {
            $this->transformSingleCriteria($filter);
        }

        return $filter;
    }

    protected function getAllowedTables(): array
    {
        return [$this->baseTable, self::MARKS_TABLE];
    }

    private function transformSingleCriteria(array &$filterNode): void
    {
        if (empty($filterNode['filterRule'])) {
            return;
        }

        $rule = $filterNode['filterRule'];

        if (empty($rule['column'])) {
            return;
        }

        $matches = [];
        if (!preg_match('/Mark\.(\d+)/', $rule['column'], $matches)) {
            return;
        }

        $rule1 = [
            'column' => 'MarksView.property_id',
            'comparator' => '===',
            'criteria' => (int)$matches[1]
        ];
        $rule2 = [
            'column' => 'MarksView.value',
            'comparator' => $rule['comparator'] ?? null,
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
}
