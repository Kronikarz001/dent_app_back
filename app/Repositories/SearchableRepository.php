<?php

namespace App\Repositories;

use App\Search\Search;
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
     * @return LengthAwarePaginator<int, TModel>
     */
    public function findSelectAllWithPagination(array $columns, array $params = []): LengthAwarePaginator
    {
        return $this->getSearchModel()->search($params, $columns);
    }
}
