<?php

declare(strict_types=1);

namespace App\Domain\FilterQuery;

use Cake\Database\Expression\QueryExpression;

class FilterQueryNode
{
    private static array $allowedOperands = ['and', 'or'];

    private array $allowedTables;
    private FilterQueryRule $filterRule;
    /** @var FilterQueryNode[] */
    private array $children = [];
    private ?string $childrensOperand = null;

    public function __construct(
        private readonly array $rawQuery
    ) {
    }

    /**
     * @throws FilterQueryException
     */
    public function getConditions(array $allowedTables): callable
    {
        $this->allowedTables = $allowedTables;
        $this->parseRawQuery();

        if ($this->isLeaf()) {
            return $this->filterRule->getCondition($this->allowedTables);
        }

        return fn(QueryExpression $exp) => match ($this->childrensOperand) {
            'and' => $exp->and($this->getChildConditions()),
            'or' => $exp->or($this->getChildConditions()),
            default => throw new FilterQueryException("Invalid filter query: Missing childrens operand."),
        };
    }

    /**
     * @throws FilterQueryException
     */
    private function parseRawQuery(): void
    {
        if (!empty($this->rawQuery['childrensOperand'])) {
            $this->setChildrensOperand();
        }

        if (!empty($this->rawQuery['filterRule'])) {
            $this->filterRule = new FilterQueryRule($this->rawQuery['filterRule']);
            return;
        }

        if (!empty($this->rawQuery['children'])) {
            $this->children = array_map(
                static fn($node) => new FilterQueryNode($node),
                $this->rawQuery['children']
            );
        }
    }

    /**
     * @throws FilterQueryException
     */
    private function setChildrensOperand(): void
    {
        $operand = $this->rawQuery['childrensOperand'];

        if (!in_array($operand, self::$allowedOperands, true)) {
            throw new FilterQueryException("Invalid operand: $operand");
        }

        $this->childrensOperand = $operand;
    }

    private function isLeaf(): bool
    {
        return !empty($this->filterRule);
    }

    /**
     * @throws FilterQueryException
     */
    private function getChildConditions(): array
    {
        return array_map(
            fn($node) => $node->getConditions($this->allowedTables),
            $this->children
        );
    }
}
