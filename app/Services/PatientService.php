<?php

namespace App\Services;

use App\Dto\PatientStatisticsDto;
use App\Exports\PatientExport;
use App\Http\Requests\ExportRequest;
use App\Mappers\PatientVisitMapper;
use App\Models\Calendar;
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
     * @param PatientVisitMapper $patientVisitMapper
     */
    public function __construct(
        private PatientRepositoryInterface $patientRepository,
        private ExportServiceInterface $exportService,
        private PhoneNumberServiceInterface $phoneNumberService,
        private CalendarRepositoryInterface $calendarRepository,
        private AuditRepositoryInterface $auditRepository,
        private PatientVisitMapper $patientVisitMapper,
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
        return $this->patientRepository->findSelectAllWithPagination(['uuid', 'first_name', 'last_name']);
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
        $leadDoctor = $this->patientRepository->findDoctor($patient);
        $lastVisit = $this->calendarRepository->findLastVisitForPatient($patient);
        $nextVisit = $this->calendarRepository->findNextVisitForPatient($patient);

        return new PatientStatisticsDto(
            $lastVisit === null ? null : $this->patientVisitMapper->map($lastVisit),
            $nextVisit === null ? null : $this->patientVisitMapper->map($nextVisit),
            $this->calendarRepository->countForPatient($patient, true),
            $this->calendarRepository->countForPatient($patient),
            $this->calendarRepository->findLastNoShowForPatient($patient)?->startsAt(),
            $leadDoctor,
            $leadDoctor === null
                ? null
                : $this->auditRepository->findLatestChangeTo($patient, 'doctor_uuid', $leadDoctor->uuid)?->created_at?->toDateString(),
        );
    }

    /**
     * @param Patient $patient
     * @return LengthAwarePaginator
     */
    public function getVisits(Patient $patient): LengthAwarePaginator
    {
        return $this->calendarRepository->findVisitsForPatient($patient)
            ->through(fn (Calendar $calendar) => $this->patientVisitMapper->map($calendar));
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
        return $this->exportService->export($request, new PatientExport($this->patientRepository->findAllWithPagination(perPage: -1)->getCollection()), Patient::getModel());
    }
}
