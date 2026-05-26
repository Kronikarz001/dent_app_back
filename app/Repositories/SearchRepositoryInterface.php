<?php

namespace App\Repositories;

use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Summary of SearchRepositoryInterface
 */
interface SearchRepositoryInterface
{
    /**
     * @param string $modelClass
     * @return Builder
     */
    public function createQuery(string $modelClass): Builder;

    /**
     * @param Builder $query
     * @param array $columns
     * @return void
     */
    public function applySelect(Builder $query, array $columns): void;

    /**
     * @param Builder $query
     * @param array $relations
     * @return void
     */
    public function with(Builder $query, array $relations): void;

    /**
     * @param Builder $query
     * @param array $counts
     * @return void
     */
    public function withCount(Builder $query, array $counts): void;

    /**
     * @param Builder $query
     * @param string $column
     * @return void
     */
    public function whereNull(Builder $query, string $column): void;

    /**
     * @param Builder $query
     * @param string $column
     * @return void
     */
    public function orWhereNull(Builder $query, string $column): void;

    /**
     * @param Builder $query
     * @param string $column
     * @return void
     */
    public function whereNotNull(Builder $query, string $column): void;

    /**
     * @param Builder $query
     * @param string $column
     * @return void
     */
    public function orWhereNotNull(Builder $query, string $column): void;

    /**
     * @param Builder $query
     * @param string $column
     * @param mixed $value
     * @return void
     */
    public function where(Builder $query, string $column, mixed $value): void;

    /**
     * @param Builder $query
     * @param string $column
     * @param mixed $value
     * @return void
     */
    public function orWhere(Builder $query, string $column, mixed $value): void;

    /**
     * @param Builder $query
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return void
     */
    public function whereOp(Builder $query, string $column, string $operator, mixed $value): void;

    /**
     * @param Builder $query
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return void
     */
    public function orWhereOp(Builder $query, string $column, string $operator, mixed $value): void;

    /**
     * @param Builder $query
     * @param string $column
     * @param array $values
     * @return void
     */
    public function whereIn(Builder $query, string $column, array $values): void;

    /**
     * @param Builder $query
     * @param string $column
     * @param array $values
     * @return void
     */
    public function orWhereIn(Builder $query, string $column, array $values): void;

    /**
     * @param Builder $query
     * @param string $relation
     * @param Closure $callback
     * @return void
     */
    public function whereHas(Builder $query, string $relation, Closure $callback): void;

    /**
     * @param Builder $query
     * @param string $relation
     * @param Closure $callback
     * @return void
     */
    public function orWhereHas(Builder $query, string $relation, Closure $callback): void;

    /**
     * @param Builder $query
     * @param string $relation
     * @return void
     */
    public function orDoesntHave(Builder $query, string $relation): void;

    /**
     * @param Builder $query
     * @param Closure $callback
     * @return void
     */
    public function whereGroup(Builder $query, Closure $callback): void;

    /**
     * @param Builder $query
     * @param Closure $callback
     * @return void
     */
    public function orWhereGroup(Builder $query, Closure $callback): void;

    /**
     * @param Builder $query
     * @param string $column
     * @param string $direction
     * @return void
     */
    public function orderBy(Builder $query, string $column, string $direction): void;

    /**
     * @param Builder $query
     * @param string $table
     * @param string $first
     * @param string $second
     * @return void
     */
    public function leftJoin(Builder $query, string $table, string $first, string $second): void;

    /**
     * @param Builder $query
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(Builder $query, int $perPage): LengthAwarePaginator;
}
