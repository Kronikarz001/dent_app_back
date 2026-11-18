<?php

namespace App\Search;

use App\Repositories\SearchRepositoryInterface;
use App\Services\SearchServiceInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

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
     * @param SearchServiceInterface $searchService
     * @param SearchRepositoryInterface $searchRepository
     */
    public function __construct(
        protected Request $request,
        protected SearchServiceInterface $searchService,
        protected SearchRepositoryInterface $searchRepository,
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
     * @return array<string, string>
     */
    protected function defaultSort(): array
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
     * @param string[] $columns
     * @return string[]
     */
    protected function selectColumns(array $columns = []): array
    {
        $table = $this->searchRepository->createQuery($this->modelClass())->getModel()->getTable();

        if ($columns === []) {
            return [$table.'.*'];
        }

        return array_map(fn (string $column) => $table.'.'.$column, $columns);
    }

    /**
     * @return string[]
     */
    protected function selectRelations(): array
    {
        return [];
    }

    /**
     * @param array $params
     * @param string[] $columns
     * @param int|null $perPage
     * @return LengthAwarePaginator
     */
    final public function search(array $params = [], array $columns = [], ?int $perPage = null): LengthAwarePaginator
    {
        return $this->searchRepository->paginate($this->buildQuery($params, $columns), $perPage ?? $this->resolvePerPage());
    }

    /**
     * @param array $params
     * @param string[] $columns
     * @return Collection
     */
    final public function searchAll(array $params = [], array $columns = []): Collection
    {
        return $this->searchRepository->get($this->buildQuery($params, $columns));
    }

    /**
     * @param array $params
     * @param string[] $columns
     * @return Builder
     */
    private function buildQuery(array $params, array $columns): Builder
    {
        $query = $this->searchRepository->createQuery($this->modelClass());

        $this->searchRepository->applySelect($query, $this->selectColumns($columns));

        $this->searchService->applyRelations(
            $query,
            $columns === [] ? array_merge(
                $this->relationsShipLoad(),
                $this->searchService->buildRecursiveRelationPaths($this->recursiveRelations())
            ) : $this->selectRelations(),
            $this->relationsCount()
        );

        $this->applyPreFilters($query, $params);
        $this->applyFilters($query);
        $this->applySearchString($query);
        $this->applySort($query);

        return $query;
    }

    /**
     * @return int
     */
    protected function resolvePerPage(): int
    {
        return (int) $this->request->get('perPage', config('search.default_per_page'));
    }

    /**
     * @param Builder $query
     * @param array $params
     * @return void
     */
    protected function applyPreFilters(Builder $query, array $params): void
    {
        $orFilters = is_array($params['or'] ?? null) ? $params['or'] : [];
        unset($params['or']);

        if (! empty($orFilters)) {
            $this->searchService->applyOrGroup($query, $orFilters, $query->getModel()->getTable(), $this->buildSearchConfig());
        }

        $params = $this->searchService->applyNullSentinels($query, $params, $this->fillableSearchFields());

        $this->preFilter($query, $params);
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

        $this->searchService->applyFilters($query, $filterData, $query->getModel()->getTable(), $this->buildSearchConfig());
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

        $this->searchService->applySearchString(
            $query,
            $this->request->get($searchKeyword),
            $this->searchStringFields(),
            $query->getModel()->getTable()
        );
    }

    /**
     * @param Builder $query
     * @return void
     */
    protected function applySort(Builder $query): void
    {
        $sortParam = $this->request->get(config('search.sort_keyword'));
        if ($sortParam === null) {
            foreach ($this->defaultSort() as $column => $direction) {
                $this->searchRepository->orderBy($query, $query->getModel()->getTable().'.'.$column, $direction);
            }

            return;
        }

        $parts = explode(',', $sortParam, 2);
        $sortField = $parts[0];
        $direction = $this->searchService->getSortDirection((string) ($parts[1] ?? ''));

        if (! str_contains($sortField, '.')) {
            $this->searchService->applyDirectSort($query, $sortField, $direction, $this->fillableSortFields(), $this->relationsCount());

            return;
        }

        if ($this->searchService->isJsonField($sortField, $this->jsonSearchableFields())) {
            $this->searchRepository->orderBy(
                $query,
                $query->getModel()->getTable().'.'.$this->searchService->jsonPathFromField($sortField),
                $direction
            );

            return;
        }

        $this->searchService->applyRelationSort($query, $sortField, $direction);
    }

    /**
     * @return SearchConfig
     */
    private function buildSearchConfig(): SearchConfig
    {
        return new SearchConfig(
            fillableSearchFields: $this->fillableSearchFields(),
            jsonSearchableFields: $this->jsonSearchableFields(),
            loadedRelations: $this->relationsShipLoad(),
            recursiveRelations: $this->recursiveRelations(),
            includeEmptyRelations: $this->includeEmptyRelations,
        );
    }
}
