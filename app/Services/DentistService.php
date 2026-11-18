<?php

namespace App\Services;

use App\Repositories\DentistRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
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
     * @return Collection
     */
    public function getDentistsList(): Collection
    {
        return $this->dentistRepository->findSelectAll(['uuid', 'first_name', 'last_name', 'job_position_uuid']);
    }
}
