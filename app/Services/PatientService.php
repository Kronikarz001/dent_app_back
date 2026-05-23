<?php

namespace App\Services;

use App\Exports\PatientExport;
use App\Http\Requests\ExportRequest;
use App\Models\Patient;
use App\Repositories\PatientRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Summary of PatientService
 */
readonly class PatientService implements PatientServiceInterface
{
    public function __construct(
        private PatientRepositoryInterface $patientRepository,
        private ExportServiceInterface $exportService,
    ) {}

    public function getPatients(): LengthAwarePaginator
    {
        return $this->patientRepository->findAllWithPagination();
    }

    public function getPatientsList(): LengthAwarePaginator
    {
        return $this->patientRepository->findAllWithPagination(['uuid', 'name']);
    }

    public function createPatient(array $data): Patient
    {
        return $this->patientRepository->create($data);
    }

    public function updatePatient(Patient $patient, array $data): Patient
    {
        return $this->patientRepository->update($patient, $data);
    }

    public function deletePatient(Patient $patient): void
    {
        $this->patientRepository->delete($patient);
    }

    /**
     * @throws Exception
     * @throws WriterException
     */
    public function export(ExportRequest $request): BinaryFileResponse
    {
        return $this->exportService->export($request, new PatientExport($this->getPatients()->getCollection()), Patient::getModel());
    }
}
