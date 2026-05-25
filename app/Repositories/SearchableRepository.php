<?php

namespace App\Repositories;

use App\Search\Search;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

abstract class SearchableRepository extends BasicRepository implements SearchableRepositoryInterface
{
    abstract protected function getSearchModel(): Search;

    public function findAllWithPagination(array $params = []): LengthAwarePaginator
    {
        return $this->getSearchModel()->search($params);
    }

    public function findSelectAllWithPagination(array $columns = ['*'], array $params = []): LengthAwarePaginator
    {
        return $this->getSearchModel()->search($params);
    }
}
