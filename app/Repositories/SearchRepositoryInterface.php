<?php
namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Summary of SearchRepositoryInterface
 */
interface SearchRepositoryInterface
{
    /**
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function findAllWithPagination(array $params = []): LengthAwarePaginator;
}
