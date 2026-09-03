<?php

namespace App\Services;

use App\Repositories\SearchRepositoryInterface;
use App\Search\SearchConfig;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

/**
 * Summary of SearchService
 */
class SearchService implements SearchServiceInterface
{
    /**
     * @param SearchRepositoryInterface $repo
     */
    public function __construct(
        private SearchRepositoryInterface $repo,
    ) {}

    /**
     * @param mixed $value
     * @return string|null
     */
    public function resolveNullFilter(mixed $value): ?string
    {
        if (is_array($value)) {
            return $this->resolveArrayNullFilter($value);
        }

        return $this->resolveScalarNullFilter($value);
    }

    /**
     * @param string $field
     * @param array $allowedJsonFields
     * @return bool
     */
    public function isJsonField(string $field, array $allowedJsonFields): bool
    {
        return ! empty($allowedJsonFields)
            && in_array(explode('.', $field, 2)[0], $allowedJsonFields, true);
    }

    /**
     * @param string $field
     * @return string
     */
    public function jsonPathFromField(string $field): string
    {
        return str_replace('.', '->', $field);
    }

    /**
     * @param string $relation
     * @param array $loadedRelations
     * @param array $recursiveRelations
     * @return bool
     */
    public function canFilterByRelation(string $relation, array $loadedRelations, array $recursiveRelations): bool
    {
        return collect($loadedRelations)
            ->contains(fn ($r) => $r === $relation || str_starts_with($r, $relation.'.'))
            || array_key_exists($relation, $recursiveRelations);
    }

    /**
     * @param string $sortField
     * @param array $relationsCount
     * @return bool
     */
    public function isRelationCountField(string $sortField, array $relationsCount): bool
    {
        return collect($relationsCount)
            ->contains(fn ($relation) => Str::snake($relation).'_count' === $sortField);
    }

    /**
     * @param string $direction
     * @return string
     */
    public function getSortDirection(string $direction): string
    {
        return config('search.sort_asc_default_character') === $direction ? 'asc' : 'desc';
    }

    /**
     * @param array $recursiveRelations
     * @return array
     */
    public function buildRecursiveRelationPaths(array $recursiveRelations): array
    {
        return collect($recursiveRelations)
            ->filter(fn ($depth) => $depth > 0)
            ->flatMap(fn (int $depth, string $relation) => $this->buildRelationDepthPaths($relation, $depth))
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * @param mixed $field
     * @param mixed $value
     * @param array $fillableFields
     * @return bool
     */
    public function isApplicableNullSentinelField(mixed $field, mixed $value, array $fillableFields): bool
    {
        return $this->resolveNullFilter($value) !== null
            && is_string($field)
            && ! str_contains($field, '.')
            && in_array($field, $fillableFields, true);
    }

    /**
     * @param Builder $query
     * @param string $relation
     * @return Relation|null
     */
    public function resolveRelation(Builder $query, string $relation): ?Relation
    {
        $model = $query->getModel();

        if (! method_exists($model, $relation)) {
            return null;
        }

        $relationObj = $model->{$relation}();

        return $relationObj instanceof Relation ? $relationObj : null;
    }

    /**
     * @param Relation $relation
     * @param string $field
     * @return string
     */
    public function qualifyRelationField(Relation $relation, string $field): string
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
     * @param array $relations
     * @param array $counts
     * @return void
     */
    public function applyRelations(Builder $query, array $relations, array $counts): void
    {
        if (! empty($relations)) {
            $this->repo->with($query, $relations);
        }

        if (! empty($counts)) {
            $this->repo->withCount($query, $counts);
        }
    }

    /**
     * @param Builder $query
     * @param array $params
     * @param array $fillableFields
     * @return array
     */
    public function applyNullSentinels(Builder $query, array $params, array $fillableFields): array
    {
        if (empty($params)) {
            return $params;
        }

        $model = $query->getModel();

        foreach ($params as $field => $value) {
            if (! $this->isApplicableNullSentinelField($field, $value, $fillableFields)) {
                continue;
            }

            $nullCondition = $this->resolveNullFilter($value);
            $qualified = $model->qualifyColumn($field);

            $nullCondition === 'null'
                ? $this->repo->whereNull($query, $qualified)
                : $this->repo->whereNotNull($query, $qualified);

            unset($params[$field]);
        }

        return $params;
    }

    /**
     * @param Builder $query
     * @param array $orFilters
     * @param string $table
     * @param SearchConfig $config
     * @return void
     */
    public function applyOrGroup(Builder $query, array $orFilters, string $table, SearchConfig $config): void
    {
        $this->repo->whereGroup($query, function (Builder $q) use ($orFilters, $table, $config) {
            foreach ($orFilters as $field => $value) {
                $this->applyFilter($q, $field, $value, $table, true, $config);
            }
        });
    }

    /**
     * @param Builder $query
     * @param array $filterData
     * @param string $table
     * @param SearchConfig $config
     * @return void
     */
    public function applyFilters(Builder $query, array $filterData, string $table, SearchConfig $config): void
    {
        if (array_key_exists('or', $filterData) && is_array($filterData['or'])) {
            $this->applyOrGroup($query, $filterData['or'], $table, $config);
            unset($filterData['or']);
        }

        foreach ($filterData as $field => $value) {
            $this->applyFilter($query, $field, $value, $table, false, $config);
        }
    }

    /**
     * @param Builder $query
     * @param string $searchString
     * @param array $fields
     * @param string $table
     * @return void
     */
    public function applySearchString(Builder $query, string $searchString, array $fields, string $table): void
    {
        $this->repo->whereGroup($query, function (Builder $q) use ($query, $searchString, $fields, $table) {
            foreach ($fields as $field) {
                $this->applySearchStringField($q, $query, $field, $searchString, $table);
            }
        });
    }

    /**
     * @param Builder $query
     * @param string $sortField
     * @param string $direction
     * @param array $fillableSortFields
     * @param array $relationsCount
     * @return void
     */
    public function applyDirectSort(Builder $query, string $sortField, string $direction, array $fillableSortFields, array $relationsCount): void
    {
        if (! in_array($sortField, $fillableSortFields, true)) {
            return;
        }

        if (str_ends_with($sortField, '_count') && $this->isRelationCountField($sortField, $relationsCount)) {
            $this->repo->orderBy($query, $sortField, $direction);

            return;
        }

        $this->repo->orderBy($query, $query->getModel()->getTable().'.'.$sortField, $direction);
    }

    /**
     * @param Builder $query
     * @param string $sortField
     * @param string $direction
     * @return void
     */
    public function applyRelationSort(Builder $query, string $sortField, string $direction): void
    {
        [$relation, $relField] = explode('.', $sortField, 2);
        $relationObj = $this->resolveRelation($query, $relation);

        if ($relationObj === null || $this->isRestrictedRelationField($relationObj, $relField)) {
            return;
        }

        if ($relationObj instanceof BelongsTo) {
            $this->sortByBelongsTo($query, $relationObj, $relation, $relField, $direction);
        } elseif ($relationObj instanceof HasOne || $relationObj instanceof HasMany) {
            $this->sortByHasOneOrMany($query, $relationObj, $relation, $relField, $direction);
        } elseif ($relationObj instanceof BelongsToMany) {
            $this->sortByBelongsToMany($query, $relationObj, $relation, $relField, $direction);
        }
    }

    private function applyFilter(Builder $query, string $field, mixed $value, string $table, bool $useOr, SearchConfig $config): void
    {
        if (! str_contains($field, '.')) {
            $this->applyDirectFieldFilter($query, $field, $value, $table, $useOr, $config);

            return;
        }

        if ($this->isJsonField($field, $config->jsonSearchableFields)) {
            $this->applyJsonFieldFilter($query, $field, $value, $table, $useOr);

            return;
        }

        [$relation, $relField] = explode('.', $field, 2);

        if (str_starts_with($relField, 'pivot.')) {
            $this->applyPivotFieldFilter($query, $relation, str_replace('pivot.', '', $relField), $value, $useOr);

            return;
        }

        if ($this->canFilterByRelation($relation, $config->loadedRelations, $config->recursiveRelations)) {
            $this->applyRelationFieldFilter($query, $relation, $relField, $value, $useOr, $config);
        }
    }

    private function applyDirectFieldFilter(Builder $query, string $field, mixed $value, string $table, bool $useOr, SearchConfig $config): void
    {
        if (! in_array($field, $config->fillableSearchFields, true)
            || in_array($field, config('search.search_keywords', []), true)) {
            return;
        }

        $qualified = $table.'.'.$field;
        $nullCondition = $this->resolveNullFilter($value);

        if ($nullCondition !== null) {
            $this->applyWhereNull($query, $qualified, $nullCondition, $useOr);

            return;
        }

        $this->applyWhereValue($query, $qualified, $value, $useOr);
    }

    private function applyJsonFieldFilter(Builder $query, string $field, mixed $value, string $table, bool $useOr): void
    {
        $qualified = $table.'.'.$this->jsonPathFromField($field);
        $nullCondition = $this->resolveNullFilter($value);

        if ($nullCondition !== null) {
            $this->applyWhereNull($query, $qualified, $nullCondition, $useOr);

            return;
        }

        $this->applyWhereValue($query, $qualified, $value, $useOr);
    }

    private function applyPivotFieldFilter(Builder $query, string $relation, string $pivotField, mixed $value, bool $useOr): void
    {
        $callback = fn (Builder $q) => is_array($value)
            ? $q->wherePivotIn($pivotField, $value)
            : $q->wherePivot($pivotField, $value);

        $this->applyWhereHas($query, $relation, $callback, $useOr);
    }

    private function applyRelationFieldFilter(Builder $query, string $relation, string $relField, mixed $value, bool $useOr, SearchConfig $config): void
    {
        $relationObj = $this->resolveRelation($query, $relation);
        if ($relationObj === null || $this->isRestrictedRelationField($relationObj, $relField)) {
            return;
        }

        $qualifiedField = $this->qualifyRelationField($relationObj, $relField);
        $nullCondition = $this->resolveNullFilter($value);

        if ($config->includeEmptyRelations) {
            $callback = function (Builder $outer) use ($relation, $qualifiedField, $value, $nullCondition): void {
                $this->repo->whereHas($outer, $relation, fn (Builder $inner) => $this->applyRelationCondition($inner, $qualifiedField, $value, $nullCondition));
                $this->repo->orDoesntHave($outer, $relation);
            };

            $useOr ? $this->repo->orWhereGroup($query, $callback) : $this->repo->whereGroup($query, $callback);

            return;
        }

        $this->applyWhereHas(
            $query,
            $relation,
            fn (Builder $q) => $this->applyRelationCondition($q, $qualifiedField, $value, $nullCondition),
            $useOr
        );
    }

    private function applyRelationCondition(Builder $query, string $qualifiedField, mixed $value, ?string $nullCondition): void
    {
        if ($nullCondition !== null) {
            $nullCondition === 'null'
                ? $this->repo->whereNull($query, $qualifiedField)
                : $this->repo->whereNotNull($query, $qualifiedField);

            return;
        }

        is_array($value)
            ? $this->repo->whereIn($query, $qualifiedField, $value)
            : $this->repo->where($query, $qualifiedField, $value);
    }

    private function applyWhereValue(Builder $query, string $column, mixed $value, bool $useOr): void
    {
        if (is_array($value)) {
            $useOr ? $this->repo->orWhereIn($query, $column, $value) : $this->repo->whereIn($query, $column, $value);

            return;
        }

        $useOr ? $this->repo->orWhere($query, $column, $value) : $this->repo->where($query, $column, $value);
    }

    private function applyWhereNull(Builder $query, string $column, string $nullType, bool $useOr): void
    {
        if ($nullType === 'null') {
            $useOr ? $this->repo->orWhereNull($query, $column) : $this->repo->whereNull($query, $column);

            return;
        }

        $useOr ? $this->repo->orWhereNotNull($query, $column) : $this->repo->whereNotNull($query, $column);
    }

    private function applyWhereHas(Builder $query, string $relation, Closure $callback, bool $useOr): void
    {
        $useOr ? $this->repo->orWhereHas($query, $relation, $callback) : $this->repo->whereHas($query, $relation, $callback);
    }

    private function applySearchStringField(Builder $q, Builder $baseQuery, string $field, string $searchString, string $table): void
    {
        $operator = $this->caseInsensitiveLikeOperator($baseQuery);

        if (! str_contains($field, '.')) {
            $this->repo->orWhereOp($q, $table.'.'.$field, $operator, '%'.$searchString.'%');

            return;
        }

        [$relation, $relField] = explode('.', $field, 2);

        if (str_starts_with($relField, 'pivot.')) {
            $pivotField = str_replace('pivot.', '', $relField);
            $this->repo->orWhereHas($q, $relation, fn (Builder $q2) => $q2->wherePivot($pivotField, $operator, '%'.$searchString.'%'));

            return;
        }

        $relationObj = $this->resolveRelation($baseQuery, $relation);
        if ($relationObj === null) {
            return;
        }

        $qualifiedField = $this->qualifyRelationField($relationObj, $relField);
        $this->repo->orWhereHas($q, $relation, fn (Builder $q2) => $this->repo->whereOp($q2, $qualifiedField, $operator, '%'.$searchString.'%'));
    }

    /**
     * ILIKE is PostgreSQL-only syntax; MySQL/SQLite use LIKE, which is
     * already case-insensitive there under the default collation.
     *
     * @param Builder $query
     * @return string
     */
    private function caseInsensitiveLikeOperator(Builder $query): string
    {
        return $query->getConnection()->getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
    }

    /**
     * Blocks sort/filter access to any relation column the related model
     * itself declares hidden (e.g. password, remember_token) — closes a
     * side-channel where a whitelisted, loaded relation could otherwise be
     * used to sort/filter by a secret column via ?sort=user.password.
     *
     * @param Relation $relation
     * @param string $relField
     * @return bool
     */
    private function isRestrictedRelationField(Relation $relation, string $relField): bool
    {
        $column = explode('->', $relField, 2)[0];
        $column = explode('.', $column)[0];

        return in_array($column, $relation->getRelated()->getHidden(), true);
    }

    private function sortByBelongsTo(Builder $query, BelongsTo $relation, string $relationName, string $relField, string $direction): void
    {
        $alias = $relationName.'_sort';
        $ownerKey = last(explode('.', $relation->getQualifiedOwnerKeyName()));

        $this->repo->leftJoin($query, "{$relation->getRelated()->getTable()} as $alias", $relation->getQualifiedForeignKeyName(), "$alias.$ownerKey");
        $this->repo->orderBy($query, "$alias.$relField", $direction);
    }

    private function sortByHasOneOrMany(Builder $query, HasOne|HasMany $relation, string $relationName, string $relField, string $direction): void
    {
        $alias = $relationName.'_sort';
        $foreignKey = last(explode('.', $relation->getQualifiedForeignKeyName()));

        $this->repo->leftJoin($query, "{$relation->getRelated()->getTable()} as $alias", $relation->getQualifiedParentKeyName(), "$alias.$foreignKey");
        $this->repo->orderBy($query, "$alias.$relField", $direction);
    }

    private function sortByBelongsToMany(Builder $query, BelongsToMany $relation, string $relationName, string $relField, string $direction): void
    {
        if (str_starts_with($relField, 'pivot.')) {
            $this->sortByPivot($query, $relation, $relationName, str_replace('pivot.', '', $relField), $direction);

            return;
        }

        $alias = $relationName.'_sort';
        $ownerKey = last(explode('.', $relation->getRelated()->getQualifiedKeyName()));

        $this->repo->leftJoin($query, "{$relation->getRelated()->getTable()} as $alias", $relation->getQualifiedForeignKeyName(), "$alias.$ownerKey");
        $this->repo->orderBy($query, "$alias.$relField", $direction);
    }

    private function sortByPivot(Builder $query, BelongsToMany $relation, string $relationName, string $pivotField, string $direction): void
    {
        $alias = $relationName.'_pivot_sort';

        $this->repo->leftJoin($query, "{$relation->getTable()} as $alias", $relation->getQualifiedParentKeyName(), $relation->getQualifiedForeignPivotKeyName());
        $this->repo->orderBy($query, "$alias.$pivotField", $direction);
    }

    private function resolveArrayNullFilter(array $value): ?string
    {
        if (array_key_exists('not_null', $value) || in_array('not_null', $value, true) || in_array('!null', $value, true)) {
            return 'not_null';
        }

        if (array_key_exists('null', $value) || in_array('null', $value, true)) {
            return 'null';
        }

        return null;
    }

    private function resolveScalarNullFilter(mixed $value): ?string
    {
        if ($value === null || $value === 'null') {
            return 'null';
        }

        if ($value === 'not_null' || $value === '!null') {
            return 'not_null';
        }

        return null;
    }

    private function buildRelationDepthPaths(string $relation, int $depth): array
    {
        return array_reduce(range(1, $depth), function (array $paths) use ($relation): array {
            $paths[] = empty($paths) ? $relation : last($paths).'.'.$relation;

            return $paths;
        }, []);
    }
}
