<?php

namespace App\Repositories;

use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator as LengthAwarePaginatorImpl;

/**
 * Summary of SearchRepository
 */
class SearchRepository implements SearchRepositoryInterface
{
    /**
     * @param string $modelClass
     * @return Builder
     */
    public function createQuery(string $modelClass): Builder
    {
        return $modelClass::query();
    }

    /**
     * @param Builder $query
     * @param array $columns
     * @return void
     */
    public function applySelect(Builder $query, array $columns): void
    {
        $query->select($columns);
    }

    /**
     * @param Builder $query
     * @param array $relations
     * @return void
     */
    public function with(Builder $query, array $relations): void
    {
        $query->with($relations);
    }

    /**
     * @param Builder $query
     * @param array $counts
     * @return void
     */
    public function withCount(Builder $query, array $counts): void
    {
        $query->withCount($counts);
    }

    /**
     * @param Builder $query
     * @param string $column
     * @return void
     */
    public function whereNull(Builder $query, string $column): void
    {
        $query->whereNull($column);
    }

    /**
     * @param Builder $query
     * @param string $column
     * @return void
     */
    public function orWhereNull(Builder $query, string $column): void
    {
        $query->orWhereNull($column);
    }

    /**
     * @param Builder $query
     * @param string $column
     * @return void
     */
    public function whereNotNull(Builder $query, string $column): void
    {
        $query->whereNotNull($column);
    }

    /**
     * @param Builder $query
     * @param string $column
     * @return void
     */
    public function orWhereNotNull(Builder $query, string $column): void
    {
        $query->orWhereNotNull($column);
    }

    /**
     * @param Builder $query
     * @param string $column
     * @param mixed $value
     * @return void
     */
    public function where(Builder $query, string $column, mixed $value): void
    {
        $query->where($column, $value);
    }

    /**
     * @param Builder $query
     * @param string $column
     * @param mixed $value
     * @return void
     */
    public function orWhere(Builder $query, string $column, mixed $value): void
    {
        $query->orWhere($column, $value);
    }

    /**
     * @param Builder $query
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return void
     */
    public function whereOp(Builder $query, string $column, string $operator, mixed $value): void
    {
        $query->where($column, $operator, $value);
    }

    /**
     * @param Builder $query
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return void
     */
    public function orWhereOp(Builder $query, string $column, string $operator, mixed $value): void
    {
        $query->orWhere($column, $operator, $value);
    }

    /**
     * @param Builder $query
     * @param string $column
     * @param array $values
     * @return void
     */
    public function whereIn(Builder $query, string $column, array $values): void
    {
        $query->whereIn($column, $values);
    }

    /**
     * @param Builder $query
     * @param string $column
     * @param array $values
     * @return void
     */
    public function orWhereIn(Builder $query, string $column, array $values): void
    {
        $query->orWhereIn($column, $values);
    }

    /**
     * @param Builder $query
     * @param string $relation
     * @param Closure $callback
     * @return void
     */
    public function whereHas(Builder $query, string $relation, Closure $callback): void
    {
        $query->whereHas($relation, $callback);
    }

    /**
     * @param Builder $query
     * @param string $relation
     * @param Closure $callback
     * @return void
     */
    public function orWhereHas(Builder $query, string $relation, Closure $callback): void
    {
        $query->orWhereHas($relation, $callback);
    }

    /**
     * @param Builder $query
     * @param string $relation
     * @return void
     */
    public function orDoesntHave(Builder $query, string $relation): void
    {
        $query->orDoesntHave($relation);
    }

    /**
     * @param Builder $query
     * @param Closure $callback
     * @return void
     */
    public function whereGroup(Builder $query, Closure $callback): void
    {
        $query->where($callback);
    }

    /**
     * @param Builder $query
     * @param Closure $callback
     * @return void
     */
    public function orWhereGroup(Builder $query, Closure $callback): void
    {
        $query->orWhere($callback);
    }

    /**
     * @param Builder $query
     * @param string $column
     * @param string $direction
     * @return void
     */
    public function orderBy(Builder $query, string $column, string $direction): void
    {
        $query->orderBy($column, $direction);
    }

    /**
     * @param Builder $query
     * @param string $table
     * @param string $first
     * @param string $second
     * @return void
     */
    public function leftJoin(Builder $query, string $table, string $first, string $second): void
    {
        $query->leftJoin($table, $first, '=', $second);
    }

    /**
     * @param Builder $query
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(Builder $query, int $perPage): LengthAwarePaginator
    {
        if ($perPage < 0) {
            $items = $query->get();

            return new LengthAwarePaginatorImpl($items, $items->count(), max($items->count(), 1));
        }

        return $query->paginate($perPage);
    }
}
