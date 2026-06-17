<?php

namespace App\Search;

use App\Repositories\SearchRepositoryInterface;
use App\Services\SearchServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

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
        $query = $this->searchRepository->createQuery($this->modelClass());

        return [$query->getModel()->getTable().'.*'];
    }

    /**
     * @param array $params
     * @return LengthAwarePaginator
     */
    final public function search(array $params = []): LengthAwarePaginator
    {
        $query = $this->searchRepository->createQuery($this->modelClass());

        $this->searchRepository->applySelect($query, $this->selectColumns());

        $this->searchService->applyRelations(
            $query,
            array_merge(
                $this->relationsShipLoad(),
                $this->searchService->buildRecursiveRelationPaths($this->recursiveRelations())
            ),
            $this->relationsCount()
        );

        $this->applyPreFilters($query, $params);
        $this->applyFilters($query);
        $this->applySearchString($query);
        $this->applySort($query);

        return $this->searchRepository->paginate($query, (int) $this->request->get('perPage', -1));
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
