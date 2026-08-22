<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of DentistServiceInterface
 */
interface DentistServiceInterface
{
    /**
     * @return LengthAwarePaginator
     */
    public function getDentists(): LengthAwarePaginator;

    /**
     * @return LengthAwarePaginator
     */
    public function getDentistsList(): LengthAwarePaginator;
}
