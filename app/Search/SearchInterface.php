<?php

namespace App\Search;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of SearchInterface
 */
interface SearchInterface
{
    /**
     * @param array $params
     * @param string[] $columns
     * @param int|null $perPage
     * @return LengthAwarePaginator
     */
    public function search(array $params = [], array $columns = [], ?int $perPage = null): LengthAwarePaginator;

    /**
     * @param array $params
     * @param string[] $columns
     * @return Collection
     */
    public function searchAll(array $params = [], array $columns = []): Collection;
}
