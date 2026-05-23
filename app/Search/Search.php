<?php

namespace App\Search;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Summary of Search
 */
abstract class Search implements SearchInterface
{
    /**
     * @var bool
     */
    protected bool $includeEmptyRelations = false;

    /**
     * @param Request $request
     */
    public function __construct(
        protected Request $request
    ) {}

    /**
     * @return string
     */
    abstract protected function modelClass(): string;

    /**
     * @return string
     */
    abstract protected function prefix(): string;

    /**
     * @return array
     */
    abstract protected function fillableSearchFields(): array;

    /**
     * @return array
     */
    abstract protected function fillableSortFields(): array;

    /**
     * @return array
     */
    abstract protected function searchStringFields(): array;

    /**
     * @param Builder $query
     * @param array $params
     * @return void
     */
    abstract protected function preFilter(Builder $query, array $params): void;

    /**
     * @return array
     */
    abstract protected function relationsShipLoad(): array;

    /**
     * @return array<string, int>
     */
    protected function recursiveRelations(): array
    {
        return [];
    }

    /**
     * @return array
     */
    protected function relationsCount(): array
    {
        return [];
    }

    /**
     * @return string[]
     */
    protected function jsonSearchableFields(): array
    {
        return [];
    }

    /**
     * @return string[]
     */
    protected function selectColumns(): array
    {
        return [
            $this->init()->getModel()->getTable().'.*',
        ];
    }

    /**
     * @param array $params
     * @return LengthAwarePaginator
     */
    final public function search(array $params = []): LengthAwarePaginator
    {
        $query = $this->init();
        $this->selectCols($query);
        $this->loadRelations($query);
        $this->applyPreFilters($query, $params);
        $this->applyFilters($query);
        $this->applySearchString($query);
        $this->applySort($query);

        $perPage = (int) $this->request->get('perPage', -1);

        return $query->paginate($perPage);
    }

    protected function applyPreFilters(Builder $query, array $params): void
    {
        if (array_key_exists('or', $params)) {
            $orFilters = $params['or'];
            unset($params['or']);

            if (is_array($orFilters)) {
                $table = $query->getModel()->getTable();
                $query->where(function (Builder $q) use ($orFilters, $table) {
                    foreach ($orFilters as $field => $value) {
                        $this->applyFilterCondition($q, $field, $value, $table, true);
                    }
                });
            }
        }

        $params = $this->applyNullSentinelsFromParams($query, $params);

        $this->preFilter($query, $params);
    }

    /**
     * @param Builder $query
     * @param array $params
     * @return array
     */
    private function applyNullSentinelsFromParams(Builder $query, array $params): array
    {
        if (empty($params)) {
            return $params;
        }

        $model = $query->getModel();

        foreach ($params as $field => $value) {
            $nullCondition = $this->resolveNullFilter($value);
            if ($nullCondition === null) {
                continue;
            }

            if (! is_string($field) || str_contains($field, '.')) {
                continue;
            }

            if (! in_array($field, $this->fillableSearchFields(), true)) {
                continue;
            }

            $qualified = $model->qualifyColumn($field);

            if ($nullCondition === 'null') {
                $query->whereNull($qualified);
                unset($params[$field]);

                continue;
            }

            if ($nullCondition === 'not_null') {
                $query->whereNotNull($qualified);
                unset($params[$field]);
            }
        }

        return $params;
    }

    private function selectCols(Builder $query): void
    {
        $query->select($this->selectColumns());
    }

    /**
     * @param Builder $query
     * @return void
     */
    protected function applyFilters(Builder $query): void
    {
        $filterData = $this->request->get($this->prefix(), []);
        if (! is_array($filterData)) {
            return;
        }

        $table = $query->getModel()->getTable();

        if (array_key_exists('or', $filterData) && is_array($filterData['or'])) {
            $orFilters = $filterData['or'];
            unset($filterData['or']);

            $query->where(function (Builder $q) use ($orFilters, $table) {
                foreach ($orFilters as $field => $value) {
                    $this->applyFilterCondition($q, $field, $value, $table, true);
                }
            });
        }

        foreach ($filterData as $field => $value) {
            $this->applyFilterCondition($query, $field, $value, $table, false);
        }
    }

    private function applyFilterCondition(Builder $query, string $field, mixed $value, string $table, bool $useOr): void
    {
        if (str_contains($field, '.')) {
            if ($this->isJsonSearchField($field)) {
                $qualified = $table.'.'.$this->jsonPathFromField($field);
                $nullCondition = $this->resolveNullFilter($value);

                if ($nullCondition === 'null') {
                    $useOr ? $query->orWhereNull($qualified) : $query->whereNull($qualified);

                    return;
                }

                if ($nullCondition === 'not_null') {
                    $useOr ? $query->orWhereNotNull($qualified) : $query->whereNotNull($qualified);

                    return;
                }

                if (is_array($value)) {
                    $useOr ? $query->orWhereIn($qualified, $value) : $query->whereIn($qualified, $value);
                } else {
                    $useOr ? $query->orWhere($qualified, $value) : $query->where($qualified, $value);
                }

                return;
            }

            [$relation, $relField] = explode('.', $field, 2);

            if (str_starts_with($relField, 'pivot.')) {
                $pivotField = str_replace('pivot.', '', $relField);
                $useOr
                    ? $query->orWhereHas($relation, function (Builder $q) use ($pivotField, $value) {
                        is_array($value)
                            ? $q->wherePivotIn($pivotField, $value)
                            : $q->wherePivot($pivotField, $value);
                    })
                    : $query->whereHas($relation, function (Builder $q) use ($pivotField, $value) {
                        is_array($value)
                            ? $q->wherePivotIn($pivotField, $value)
                            : $q->wherePivot($pivotField, $value);
                    });

                return;
            }

            if ($this->canFilterByRelation($relation)) {
                $relationObj = $this->resolveRelation($query, $relation);
                if ($relationObj === null) {
                    return;
                }
                $qualifiedRelationField = $this->qualifyRelationField($relationObj, $relField);
                $nullCondition = $this->resolveNullFilter($value);

                if ($this->includeEmptyRelations) {
                    $callback = function (Builder $q) use ($relation, $qualifiedRelationField, $value, $nullCondition) {
                        $q->whereHas($relation, function (Builder $sub) use ($qualifiedRelationField, $value, $nullCondition) {
                            if ($nullCondition === 'null') {
                                $sub->whereNull($qualifiedRelationField);

                                return;
                            }

                            if ($nullCondition === 'not_null') {
                                $sub->whereNotNull($qualifiedRelationField);

                                return;
                            }

                            is_array($value)
                                ? $sub->whereIn($qualifiedRelationField, $value)
                                : $sub->where($qualifiedRelationField, $value);
                        })->orDoesntHave($relation);
                    };

                    $useOr ? $query->orWhere($callback) : $query->where($callback);
                } else {
                    if ($nullCondition === 'null') {
                        $useOr
                            ? $query->orWhereHas($relation, function (Builder $q) use ($qualifiedRelationField) {
                                $q->whereNull($qualifiedRelationField);
                            })
                            : $query->whereHas($relation, function (Builder $q) use ($qualifiedRelationField) {
                                $q->whereNull($qualifiedRelationField);
                            });

                        return;
                    }

                    if ($nullCondition === 'not_null') {
                        $useOr
                            ? $query->orWhereHas($relation, function (Builder $q) use ($qualifiedRelationField) {
                                $q->whereNotNull($qualifiedRelationField);
                            })
                            : $query->whereHas($relation, function (Builder $q) use ($qualifiedRelationField) {
                                $q->whereNotNull($qualifiedRelationField);
                            });

                        return;
                    }

                    $useOr
                        ? $query->orWhereHas($relation, function (Builder $q) use ($qualifiedRelationField, $value) {
                            is_array($value)
                                ? $q->whereIn($qualifiedRelationField, $value)
                                : $q->where($qualifiedRelationField, $value);
                        })
                        : $query->whereHas($relation, function (Builder $q) use ($qualifiedRelationField, $value) {
                            is_array($value)
                                ? $q->whereIn($qualifiedRelationField, $value)
                                : $q->where($qualifiedRelationField, $value);
                        });
                }
            }

            return;
        }

        if (in_array($field, $this->fillableSearchFields(), true)
            && ! in_array($field, config('search.search_keywords', []), true)) {
            $qualified = $table.'.'.$field;
            $nullCondition = $this->resolveNullFilter($value);

            if ($nullCondition === 'null') {
                $useOr ? $query->orWhereNull($qualified) : $query->whereNull($qualified);

                return;
            }

            if ($nullCondition === 'not_null') {
                $useOr ? $query->orWhereNotNull($qualified) : $query->whereNotNull($qualified);

                return;
            }

            if (is_array($value)) {
                $useOr ? $query->orWhereIn($qualified, $value) : $query->whereIn($qualified, $value);
            } else {
                $useOr ? $query->orWhere($qualified, $value) : $query->where($qualified, $value);
            }
        }
    }

    /**
     * Detects null/not-null sentinel values from request filters.
     */
    private function resolveNullFilter(mixed $value): ?string
    {
        if (is_array($value)) {
            if (array_key_exists('not_null', $value) || in_array('not_null', $value, true) || in_array('!null', $value, true)) {
                return 'not_null';
            }

            if (array_key_exists('null', $value) || in_array('null', $value, true)) {
                return 'null';
            }

            return null;
        }

        if ($value === null || $value === 'null') {
            return 'null';
        }

        if ($value === 'not_null' || $value === '!null') {
            return 'not_null';
        }

        return null;
    }

    private function isJsonSearchField(string $field): bool
    {
        $allowed = $this->jsonSearchableFields();
        if (empty($allowed)) {
            return false;
        }

        $root = explode('.', $field, 2)[0];

        return in_array($root, $allowed, true);
    }

    private function jsonPathFromField(string $field): string
    {
        return str_replace('.', '->', $field);
    }

    /**
     * @param Builder $query
     * @return void
     */
    protected function applySort(Builder $query): void
    {
        $sortKeyword = config('search.sort_keyword');
        $allParams = $this->request->all();

        if (! isset($allParams[$sortKeyword])) {
            return;
        }

        [$sortField, $sortDir] = explode(',', $allParams[$sortKeyword]) + [null, null];
        $direction = $this->getSortDirection($sortDir);

        if (str_contains($sortField, '.')) {
            if ($this->isJsonSearchField($sortField)) {
                $qualified = $query->getModel()->getTable().'.'.$this->jsonPathFromField($sortField);
                $query->orderBy($qualified, $direction);

                return;
            }

            [$relation, $relField] = explode('.', $sortField, 2);
            $relationObj = $query->getModel()->{$relation}();

            if ($relationObj instanceof BelongsTo) {
                $relatedTable = $relationObj->getRelated()->getTable();
                $foreignKey = $relationObj->getQualifiedForeignKeyName();
                $ownerKey = $relationObj->getQualifiedOwnerKeyName();

                $alias = $relation.'_sort';
                $query->leftJoin("$relatedTable as $alias", $foreignKey, '=', "$alias.".last(explode('.', $ownerKey)));
                $query->orderBy("$alias.$relField", $direction);

                return;
            }

            if ($relationObj instanceof HasOne || $relationObj instanceof HasMany) {
                $relatedTable = $relationObj->getRelated()->getTable();
                $localKey = $relationObj->getQualifiedParentKeyName();
                $foreignKey = $relationObj->getQualifiedForeignKeyName();

                $alias = $relation.'_sort';
                $query->leftJoin("$relatedTable as $alias", $localKey, '=', "$alias.".last(explode('.', $foreignKey)));
                $query->orderBy("$alias.$relField", $direction);

                return;
            }

            if ($relationObj instanceof BelongsToMany) {
                if (str_starts_with($relField, 'pivot.')) {
                    $pivotField = str_replace('pivot.', '', $relField);
                    $pivotTable = $relationObj->getTable();
                    $parentKey = $relationObj->getQualifiedParentKeyName();
                    $foreignPivotKey = $relationObj->getQualifiedForeignPivotKeyName();

                    $alias = $relation.'_pivot_sort';
                    $query->leftJoin("$pivotTable as $alias", $parentKey, '=', $foreignPivotKey);
                    $query->orderBy("$alias.$pivotField", $direction);

                    return;
                }

                $relatedTable = $relationObj->getRelated()->getTable();
                $foreignKey = $relationObj->getQualifiedForeignKeyName();
                $ownerKey = $relationObj->getRelated()->getQualifiedKeyName();

                $alias = $relation.'_sort';
                $query->leftJoin("$relatedTable as $alias", $foreignKey, '=', "$alias.".last(explode('.', $ownerKey)));
                $query->orderBy("$alias.$relField", $direction);

                return;
            }
        }

        if (in_array($sortField, $this->fillableSortFields(), true)) {
            if (str_ends_with($sortField, '_count') && $this->isRelationCountField($sortField)) {
                $query->orderBy($sortField, $direction);

                return;
            }

            $qualified = $query->getModel()->getTable().'.'.$sortField;
            $query->orderBy($qualified, $direction);
        }
    }

    /**
     * @param Builder $query
     * @return void
     */
    protected function applySearchString(Builder $query): void
    {
        $searchKeyword = config('search.search_string_keyword', 'search');
        if (! $this->request->filled($searchKeyword)) {
            return;
        }

        $searchString = $this->request->get($searchKeyword);
        $fields = $this->searchStringFields();
        $table = $query->getModel()->getTable();

        $query->where(function (Builder $q) use ($query, $fields, $searchString, $table) {
            foreach ($fields as $field) {
                if (str_contains($field, '.')) {
                    [$relation, $relField] = explode('.', $field, 2);

                    if (str_starts_with($relField, 'pivot.')) {
                        $pivotField = str_replace('pivot.', '', $relField);
                        $q->orWhereHas($relation, function (Builder $q2) use ($pivotField, $searchString) {
                            $q2->wherePivot($pivotField, 'ILIKE', "%{$searchString}%");
                        });

                        continue;
                    }

                    $relationObj = $this->resolveRelation($query, $relation);
                    if ($relationObj === null) {
                        continue;
                    }
                    $qualifiedRelationField = $this->qualifyRelationField($relationObj, $relField);

                    $q->orWhereHas($relation, function (Builder $q2) use ($qualifiedRelationField, $searchString) {
                        $q2->where($qualifiedRelationField, 'ILIKE', "%{$searchString}%");
                    });
                } else {
                    $qualified = $table.'.'.$field;
                    $q->orWhere($qualified, 'ILIKE', "%{$searchString}%");
                }
            }
        });
    }

    /**
     * @param string $sortDirection
     * @return string
     */
    protected function getSortDirection(string $sortDirection): string
    {
        return config('search.sort_asc_default_character') === $sortDirection ? 'asc' : 'desc';
    }

    private function canFilterByRelation(string $relation): bool
    {
        foreach ($this->relationsShipLoad() as $loadedRelation) {
            if ($loadedRelation === $relation || str_starts_with($loadedRelation, $relation.'.')) {
                return true;
            }
        }

        return array_key_exists($relation, $this->recursiveRelations());
    }

    /**
     * @param string $sortField
     * @return bool
     */
    private function isRelationCountField(string $sortField): bool
    {
        foreach ($this->relationsCount() as $relation) {
            if (Str::snake($relation).'_count' === $sortField) {
                return true;
            }
        }

        return false;
    }

    private function resolveRelation(Builder $query, string $relation): ?Relation
    {
        $model = $query->getModel();

        if (! method_exists($model, $relation)) {
            return null;
        }

        $relationObj = $model->{$relation}();

        return $relationObj instanceof Relation ? $relationObj : null;
    }

    private function qualifyRelationField(Relation $relation, string $field): string
    {
        if (str_contains($field, '.')) {
            return $field;
        }

        if (str_contains($field, '->')) {
            [$column, $jsonPath] = explode('->', $field, 2);

            return $relation->getRelated()->qualifyColumn($column).'->'.$jsonPath;
        }

        return $relation->getRelated()->qualifyColumn($field);
    }

    /**
     * @param Builder $query
     * @return void
     */
    private function loadRelations(Builder $query): void
    {
        $relations = $this->relationsShipLoad();
        if (! empty($relations)) {
            $query->with($relations);
        }

        $recursivePaths = $this->recursiveRelationPaths();
        foreach ($recursivePaths as $path) {
            $query->with($path);
        }

        if (! empty($this->relationsCount())) {
            $query->withCount($this->relationsCount());
        }
    }

    private function recursiveRelationPaths(): array
    {
        $paths = [];

        foreach ($this->recursiveRelations() as $relation => $depth) {
            if ($depth <= 0) {
                continue;
            }
            $current = $relation;
            for ($level = 1; $level <= $depth; $level++) {
                $paths[] = $current;
                $current .= '.'.$relation;
            }
        }

        return array_values(array_unique($paths));
    }

    /**
     * @return Builder
     */
    private function init(): Builder
    {
        $modelClass = $this->modelClass();

        return $modelClass::query();
    }
}
