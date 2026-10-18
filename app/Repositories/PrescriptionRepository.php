<?php

namespace App\Repositories;

use App\Models\Prescription;
use App\Search\PrescriptionSearch;
use App\Search\Search;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of PrescriptionRepository
 */
class PrescriptionRepository extends SearchableRepository implements PrescriptionRepositoryInterface
{
    /**
     * @var string
     */
    protected string $modelClass = Prescription::class;

    /**
     * @param PrescriptionSearch $search
     */
    public function __construct(
        private PrescriptionSearch $search
    ) {}

    /**
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function findAllWithPagination(array $params = []): LengthAwarePaginator
    {
        return $this->search->search($params);
    }

    /**
     * @return Search
     */
    protected function getSearchModel(): Search
    {
        return $this->search;
    }

    /**
     * @param string $uuid
     * @return Prescription|null
     */
    public function findByUuid(string $uuid): ?Prescription
    {
        return Prescription::where('uuid', $uuid)->first();
    }

    /**
     * @param string $patientUuid
     * @return LengthAwarePaginator
     */
    public function findAllByPatient(string $patientUuid): LengthAwarePaginator
    {
        return Prescription::query()
            ->where('patient_uuid', $patientUuid)
            ->with(['patient', 'doctor', 'medicines'])
            ->latest()
            ->paginate();
    }

    /**
     * @param array $data
     * @return Prescription
     */
    public function create(array $data): Prescription
    {
        return Prescription::create($data);
    }

    /**
     * @param Prescription|Model $model
     * @param array $data
     * @return Prescription
     */
    public function update(Prescription|Model $model, array $data): Prescription
    {
        $model->update($data);

        return $model->fresh();
    }

    /**
     * @param Prescription|Model $model
     * @return bool
     */
    public function delete(Prescription|Model $model): bool
    {
        return $model->delete();
    }
}
