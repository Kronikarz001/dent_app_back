<?php

namespace App\Services;

use App\Search\SearchConfig;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Summary of SearchServiceInterface
 */
interface SearchServiceInterface
{
    /**
     * @param mixed $value
     * @return string|null
     */
    public function resolveNullFilter(mixed $value): ?string;

    /**
     * @param string $field
     * @param array $allowedJsonFields
     * @return bool
     */
    public function isJsonField(string $field, array $allowedJsonFields): bool;

    /**
     * @param string $field
     * @return string
     */
    public function jsonPathFromField(string $field): string;

    /**
     * @param string $relation
     * @param array $loadedRelations
     * @param array $recursiveRelations
     * @return bool
     */
    public function canFilterByRelation(string $relation, array $loadedRelations, array $recursiveRelations): bool;

    /**
     * @param string $sortField
     * @param array $relationsCount
     * @return bool
     */
    public function isRelationCountField(string $sortField, array $relationsCount): bool;

    /**
     * @param string $direction
     * @return string
     */
    public function getSortDirection(string $direction): string;

    /**
     * @param array $recursiveRelations
     * @return array
     */
    public function buildRecursiveRelationPaths(array $recursiveRelations): array;

    /**
     * @param mixed $field
     * @param mixed $value
     * @param array $fillableFields
     * @return bool
     */
    public function isApplicableNullSentinelField(mixed $field, mixed $value, array $fillableFields): bool;

    /**
     * @param Builder $query
     * @param string $relation
     * @return Relation|null
     */
    public function resolveRelation(Builder $query, string $relation): ?Relation;

    /**
     * @param Relation $relation
     * @param string $field
     * @return string
     */
    public function qualifyRelationField(Relation $relation, string $field): string;

    /**
     * @param Builder $query
     * @param array $relations
     * @param array $counts
     * @return void
     */
    public function applyRelations(Builder $query, array $relations, array $counts): void;

    /**
     * @param Builder $query
     * @param array $params
     * @param array $fillableFields
     * @return array
     */
    public function applyNullSentinels(Builder $query, array $params, array $fillableFields): array;

    /**
     * @param Builder $query
     * @param array $orFilters
     * @param string $table
     * @param SearchConfig $config
     * @return void
     */
    public function applyOrGroup(Builder $query, array $orFilters, string $table, SearchConfig $config): void;

    /**
     * @param Builder $query
     * @param array $filterData
     * @param string $table
     * @param SearchConfig $config
     * @return void
     */
    public function applyFilters(Builder $query, array $filterData, string $table, SearchConfig $config): void;

    /**
     * @param Builder $query
     * @param string $searchString
     * @param array $fields
     * @param string $table
     * @return void
     */
    public function applySearchString(Builder $query, string $searchString, array $fields, string $table): void;

    /**
     * @param Builder $query
     * @param string $sortField
     * @param string $direction
     * @param array $fillableSortFields
     * @param array $relationsCount
     * @return void
     */
    public function applyDirectSort(Builder $query, string $sortField, string $direction, array $fillableSortFields, array $relationsCount): void;

    /**
     * @param Builder $query
     * @param string $sortField
     * @param string $direction
     * @return void
     */
    public function applyRelationSort(Builder $query, string $sortField, string $direction): void;
}
