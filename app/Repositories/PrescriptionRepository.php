<?php

namespace App\Repositories;

use App\Models\Prescription;
use App\Search\PrescriptionSearch;
use App\Search\Search;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of PrescriptionRepository
 *
 * @extends SearchableRepository<Prescription>
 */
class PrescriptionRepository extends SearchableRepository implements PrescriptionRepositoryInterface
{
    /**
     * @var class-string<Prescription>
     */
    protected string $modelClass = Prescription::class;

    /**
     * @param PrescriptionSearch $search
     */
    public function __construct(
        private readonly PrescriptionSearch $search
    ) {}

    /**
     * @return Search
     */
    protected function getSearchModel(): Search
    {
        return $this->search;
    }

    /**
     * @param string $patientUuid
     * @return LengthAwarePaginator<int, Prescription>
     */
    public function findAllByPatient(string $patientUuid): LengthAwarePaginator
    {
        return $this->search->search(['patient_uuid' => $patientUuid]);
    }
}
