<?php

namespace App\Services;

use App\Dto\PatientStatisticsDto;
use App\Exports\PatientExport;
use App\Http\Requests\ExportRequest;
use App\Models\Patient;
use App\Repositories\AuditRepositoryInterface;
use App\Repositories\CalendarRepositoryInterface;
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
    /**
     * @param PatientRepositoryInterface $patientRepository
     * @param ExportServiceInterface $exportService
     * @param PhoneNumberServiceInterface $phoneNumberService
     * @param CalendarRepositoryInterface $calendarRepository
     * @param AuditRepositoryInterface $auditRepository
     */
    public function __construct(
        private PatientRepositoryInterface $patientRepository,
        private ExportServiceInterface $exportService,
        private PhoneNumberServiceInterface $phoneNumberService,
        private CalendarRepositoryInterface $calendarRepository,
        private AuditRepositoryInterface $auditRepository,
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    public function getPatients(): LengthAwarePaginator
    {
        return $this->patientRepository->findAllWithPagination();
    }

    /**
     * @return LengthAwarePaginator
     */
    public function getPatientsList(): LengthAwarePaginator
    {
        return $this->patientRepository->findAllWithPagination(['uuid', 'name']);
    }

    /**
     * @return LengthAwarePaginator
     */
    public function getPatientsWithRomexisId(): LengthAwarePaginator
    {
        return $this->patientRepository->findAllWithRomexisId();
    }

    /**
     * @param array $data
     * @return Patient
     */
    public function createPatient(array $data): Patient
    {
        return $this->patientRepository->create($data);
    }

    /**
     * @param Patient $patient
     * @param array $data
     * @return Patient
     */
    public function updatePatient(Patient $patient, array $data): Patient
    {
        if (array_key_exists('phone_numbers', $data)) {
            $this->phoneNumberService->assignPhone($patient, $data['phone_numbers'] ?? []);
        }

        return $this->patientRepository->update($patient, $data);
    }

    /**
     * @param Patient $patient
     * @return void
     */
    public function deletePatient(Patient $patient): void
    {
        $this->patientRepository->delete($patient);
    }

    /**
     * @param Patient $patient
     * @return PatientStatisticsDto
     */
    public function getStatistics(Patient $patient): PatientStatisticsDto
    {
        $leadDoctor = $patient->doctor()->with('jobPosition')->first();

        return new PatientStatisticsDto(
            $this->calendarRepository->findLastVisitForPatient($patient),
            $this->calendarRepository->findNextVisitForPatient($patient),
            $this->calendarRepository->countForPatient($patient, true),
            $this->calendarRepository->countForPatient($patient),
            $this->calendarRepository->findLastNoShowForPatient($patient),
            $leadDoctor,
            $leadDoctor === null
                ? null
                : $this->auditRepository->findLatestChangeTo($patient, 'doctor_uuid', $leadDoctor->uuid)?->created_at,
        );
    }

    /**
     * @param ExportRequest $request
     * @return BinaryFileResponse
     *
     * @throws Exception
     * @throws WriterException
     */
    public function export(ExportRequest $request): BinaryFileResponse
    {
        return $this->exportService->export($request, new PatientExport($this->getPatients()->getCollection()), Patient::getModel());
    }
}
