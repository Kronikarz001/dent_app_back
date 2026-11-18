<?php

namespace App\Repositories;

use App\Search\Search;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of SearchableRepository
 *
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends BasicRepository<TModel>
 *
 * @implements SearchableRepositoryInterface<TModel>
 */
abstract class SearchableRepository extends BasicRepository implements SearchableRepositoryInterface
{
    /**
     * @return Search
     */
    abstract protected function getSearchModel(): Search;

    /**
     * @param array $params
     * @param int|null $perPage
     * @return LengthAwarePaginator<int, TModel>
     */
    public function findAllWithPagination(array $params = [], ?int $perPage = null): LengthAwarePaginator
    {
        return $this->getSearchModel()->search($params, perPage: $perPage);
    }

    /**
     * @param string[] $columns
     * @param array $params
     * @return Collection<int, TModel>
     */
    public function findSelectAll(array $columns, array $params = []): Collection
    {
        return $this->getSearchModel()->searchAll($params, $columns);
    }

    /**
     * @param array $params
     * @return Collection<int, TModel>
     */
    public function findAllWithoutPagination(array $params = []): Collection
    {
        return $this->getSearchModel()->searchAll($params);
    }
}
