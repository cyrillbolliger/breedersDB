<?php

declare(strict_types=1);

namespace App\Domain\FilterQuery;

class MarkableFilterQueryBuilder extends FilterQueryBuilder
{
    private const MARKS_TABLE = 'MarksView';

    protected function buildQuery(): void
    {
        $this->setTable();

        $this->query = $this->table
            ->find()
            ->leftJoinWith(self::MARKS_TABLE);

        if (!empty($this->rawQuery['baseFilter'])) {
            $this->addWhere();
        }

        // todo: filter by markFilter
    }

    /**
     * @throws FilterQueryException
     */
    protected function addWhere(): void
    {
        $filterData = $this->transformMarkCriteriaRecursive($this->rawQuery['baseFilter']);
        $filterQuery = new FilterQueryNode($filterData);
        $this->query->where($filterQuery->getConditions($this->getAllowedTables()));
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
