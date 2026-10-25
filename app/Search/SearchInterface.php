<?php

namespace App\Search;

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
}
