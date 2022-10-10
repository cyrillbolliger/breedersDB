<?php

declare(strict_types=1);

namespace App\Domain\FilterQuery;

use App\Domain\FilterQuery\FilterQueryComparator as Comparator;
use Cake\Database\Expression\QueryExpression;

class FilterQueryRule
{
    private string $column;
    private Comparator $comparator;
    private float|string|null $criteria;

    private array $allowedTables;

    public function __construct(
        private readonly array $rawRule
    ) {
    }

    /**
     * @throws FilterQueryException
     */
    public function getCondition(array $allowedTables): callable
    {
        $this->allowedTables = $allowedTables;
        $this->parse();
        return fn(QueryExpression $exp) => $this->buildConditionExpression($exp);
    }

    /**
     * @throws FilterQueryException
     */
    private function parse(): void
    {
        $this->parseColumn();
        $this->parseComparator();
        $this->parseCriteria();
    }

    /**
     * @throws FilterQueryException
     */
    private function parseColumn(): void
    {
        if (empty($this->rawRule['column'])) {
            throw new FilterQueryException('Invalid filter rule: empty column.');
        }

        $column = $this->rawRule['column'];
        $table = explode('.', $column)[0];

        if (!in_array($table, $this->allowedTables, true)) {
            throw new FilterQueryException("Invalid column in filter rule: $column");
        }

        $this->column = $column;
    }

    /**
     * @throws FilterQueryException
     */
    private function parseComparator(): void
    {
        if (empty($this->rawRule['comparator'])) {
            throw new FilterQueryException('Invalid filter rule: empty comparator.');
        }

        $comparator = Comparator::tryFrom($this->rawRule['comparator']);

        if ($comparator === null) {
            throw new FilterQueryException("Invalid comparator in filter rule: {$this->rawRule['comparator']}");
        }

        $this->comparator = $comparator;
    }

    private function buildConditionExpression(QueryExpression $exp): QueryExpression
    {
        $column = $this->column;
        $criteria = $this->criteria;

        return match ($this->comparator) {
            Comparator::Equal => $exp->eq($column, $criteria),
            Comparator::NotEqual => $exp->notEq($column, $criteria),
            Comparator::StartsWith => $exp->like($column, "$criteria%"),
            Comparator::StartsNotWith => $exp->notLike($column, "$criteria%"),
            Comparator::Contains => $exp->like($column, "%$criteria%"),
            Comparator::NotContains => $exp->notLike($column, "%$criteria%"),
            Comparator::EndsWith => $exp->like($column, "%$criteria"),
            Comparator::NotEndsWith => $exp->notLike($column, "%$criteria"),

            Comparator::Less => $exp->lt($column, $criteria),
            Comparator::LessOrEqual => $exp->lte($column, $criteria),
            Comparator::Greater => $exp->gt($column, $criteria),
            Comparator::GreaterOrEqual => $exp->gte($column, $criteria),

            Comparator::Empty => $exp->or($exp->isNull($column)->eq($column, '')),
            Comparator::NotEmpty => $exp->and($exp->isNotNull($column)->notEq($column, '')),

            Comparator::True => $exp->eq($column, true),
            Comparator::False => $exp->eq($column, false),
        };
    }

    private function parseCriteria(): void
    {
        $criteria = $this->rawRule['criteria'] ?? null;

        $this->criteria = match ($this->comparator) {
            Comparator::Equal,
            Comparator::NotEqual,
            Comparator::StartsWith,
            Comparator::StartsNotWith,
            Comparator::Contains,
            Comparator::NotContains,
            Comparator::EndsWith,
            Comparator::NotEndsWith => $criteria ?? '',

            Comparator::Less,
            Comparator::LessOrEqual,
            Comparator::Greater,
            Comparator::GreaterOrEqual => $criteria ?? 0,

            Comparator::Empty,
            Comparator::NotEmpty,
            Comparator::True,
            Comparator::False => null,
        };
    }


}
