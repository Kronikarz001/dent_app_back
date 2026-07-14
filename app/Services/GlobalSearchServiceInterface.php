<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Summary of GlobalSearchServiceInterface
 */
interface GlobalSearchServiceInterface
{
    /**
     * @param array<int, string>|null $moduleValues
     * @return LengthAwarePaginator
     */
    public function search(?array $moduleValues): LengthAwarePaginator;
}
