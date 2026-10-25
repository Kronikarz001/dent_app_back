<?php

namespace App\Repositories;

use App\Models\Prescription;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of PrescriptionRepositoryInterface
 *
 * @extends SearchableRepositoryInterface<Prescription>
 */
interface PrescriptionRepositoryInterface extends SearchableRepositoryInterface
{
    /**
     * @param string $patientUuid
     * @return LengthAwarePaginator<int, Prescription>
     */
    public function findAllByPatient(string $patientUuid): LengthAwarePaginator;
}
