<?php

namespace App\Core;

class QueryBuilder
{
    protected string $table;
    private array $selectColumns = ['*'];
    private array $whereConditions = [];
    private array $joinConditions = [];
    private ?string $orderBy = null;
    private ?string $limit = null;
    private ?string $offset = null;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function select(array $columns): self
    {
        $this->selectColumns = $columns;
        return $this;
    }

    public function between(string $column, $start, $end): self
    {
        $this->whereConditions[] = "{$column} BETWEEN ? AND ?";
        return $this;
    }

    public function isGreaterThan(string $column, $value): self
    {
        $this->where($column, '>', $value);
        return $this;
    }

    public function where(string $column, string $operator, $value): self
    {
        $this->whereConditions[] = "{$column} {$operator} ?";
        return $this;
    }

    public function isLessThan(string $column, $value): self
    {
        $this->where($column, '<', $value);
        return $this;
    }

    public function isGreaterThanOrEquals(string $column, $value): self
    {
        $this->where($column, '>=', $value);
        return $this;
    }

    public function isLessThanOrEquals(string $column, $value): self
    {
        $this->where($column, '<=', $value);
        return $this;
    }

    public function contains(string $column, $value): self
    {
        $this->where($column, 'LIKE', "%{$value}%");
        return $this;
    }

    public function containingIgnoreCase(string $column, $value): self
    {
        $this->where("LOWER({$column})", 'LIKE', '%' . strtolower($value) . '%');
        return $this;
    }

    public function join(string $table, callable $onCondition): self
    {
        $onClause = $onCondition();
        $this->joinConditions[] = "JOIN {$table} ON {$onClause}";
        return $this;
    }

    public function leftJoin(string $table, callable $onCondition): self
    {
        $onClause = $onCondition();
        $this->joinConditions[] = "LEFT JOIN {$table} ON {$onClause}";
        return $this;
    }

    public function rightJoin(string $table, callable $onCondition): self
    {
        $onClause = $onCondition();
        $this->joinConditions[] = "RIGHT JOIN {$table} ON {$onClause}";
        return $this;
    }

    public function innerJoin(string $table, callable $onCondition): self
    {
        $onClause = $onCondition();
        $this->joinConditions[] = "INNER JOIN {$table} ON {$onClause}";
        return $this;
    }

    public function fullOuterJoin(string $table, callable $onCondition, string $leftAlias = "p1", string $rightAlias = "p2"): self
    {
        $onClause = $onCondition();
        $this->joinConditions[] = "LEFT JOIN {$table} AS {$leftAlias} ON {$onClause}";
        $this->joinConditions[] = "RIGHT JOIN {$table} AS {$rightAlias} ON {$onClause}";
        return $this;
    }



    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy = "ORDER BY {$column} {$direction}";
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = "LIMIT {$limit}";
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = "OFFSET {$offset}";
        return $this;
    }

    public function buildQuery(): string
    {
        $query = "SELECT " . implode(', ', $this->selectColumns) . " FROM {$this->table}";

        if (!empty($this->joinConditions)) {
            $query .= ' ' . implode(' ', $this->joinConditions);
        }

        if (!empty($this->whereConditions)) {
            $query .= ' WHERE ' . implode(' AND ', $this->whereConditions);
        }

        if ($this->orderBy) {
            $query .= ' ' . $this->orderBy;
        }

        if ($this->limit) {
            $query .= ' ' . $this->limit;
        }

        if ($this->offset) {
            $query .= ' ' . $this->offset;
        }

        return $query;
    }

    public function getParameters(): array
    {
        $parameters = [];
        foreach ($this->whereConditions as $condition) {
            if (preg_match_all('/\?/', $condition, $matches)) {
                $parameters = array_merge($parameters, array_fill(0, count($matches[0]), null));
            }
        }
        return $parameters;
    }
}
