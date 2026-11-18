<?php

namespace App\Repositories;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of PatientRepositoryInterface
 *
 * @extends SearchableRepositoryInterface<Patient>
 */
interface PatientRepositoryInterface extends SearchableRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, Patient>
     */
    public function findAllWithRomexisId(): LengthAwarePaginator;

    /**
     * @param Patient $patient
     * @return User|null
     */
    public function findDoctor(Patient $patient): ?User;
}
