<?php

namespace App\Repositories;

use App\Models\Patient;
use App\Models\User;
use App\Search\PatientSearch;
use App\Search\Search;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of PatientRepository
 *
 * @extends SearchableRepository<Patient>
 */
class PatientRepository extends SearchableRepository implements PatientRepositoryInterface
{
    /**
     * @var class-string<Patient>
     */
    protected string $modelClass = Patient::class;

    /**
     * @param PatientSearch $search
     */
    public function __construct(
        private readonly PatientSearch $search
    ) {}

    /**
     * @return Search
     */
    protected function getSearchModel(): Search
    {
        return $this->search;
    }

    /**
     * @return LengthAwarePaginator<int, Patient>
     */
    public function findAllWithRomexisId(): LengthAwarePaginator
    {
        return $this->search->search(['romexis_patient_id' => 'not_null'], ['uuid', 'romexis_patient_id']);
    }

    /**
     * @param Patient $patient
     * @return User|null
     */
    public function findDoctor(Patient $patient): ?User
    {
        return $patient->doctor()->with('jobPosition')->first();
    }
}
