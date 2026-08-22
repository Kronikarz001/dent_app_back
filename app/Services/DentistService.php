<?php

namespace App\Services;

use App\Repositories\DentistRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of DentistService
 */
readonly class DentistService implements DentistServiceInterface
{
    /**
     * @param DentistRepositoryInterface $dentistRepository
     */
    public function __construct(
        private DentistRepositoryInterface $dentistRepository
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    public function getDentists(): LengthAwarePaginator
    {
        return $this->dentistRepository->findAllWithPagination();
    }

    /**
     * @return LengthAwarePaginator
     */
    public function getDentistsList(): LengthAwarePaginator
    {
        return $this->dentistRepository->findAllWithPagination(['uuid', 'name']);
    }
}
