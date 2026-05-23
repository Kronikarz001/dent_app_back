<?php

namespace App\Search;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Summary of SearchInterface
 */
interface SearchInterface
{
    public function search(array $params = []): LengthAwarePaginator;
}
