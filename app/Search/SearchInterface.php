<?php

namespace App\Search;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Summary of SearchInterface
 */
interface SearchInterface
{
    /**
     * @param  array  $params
     * @return LengthAwarePaginator
     */
    public function search(array $params = []): LengthAwarePaginator;
}
