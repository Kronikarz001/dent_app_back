<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
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
     * @return Collection
     */
    public function getDentistsList(): Collection;
}
