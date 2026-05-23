<?php

namespace App\Services;

use App\Http\Requests\ExportRequest;
use App\Models\Patient;
use Illuminate\Pagination\LengthAwarePaginator;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Summary of PatientServiceInterface
 */
interface PatientServiceInterface
{
    /**
     * @return LengthAwarePaginator
     */
    public function getPatients(): LengthAwarePaginator;

    /**
     * @return LengthAwarePaginator
     */
    public function getPatientsList(): LengthAwarePaginator;

    /**
     * @param  array  $data
     * @return Patient
     */
    public function createPatient(array $data): Patient;

    /**
     * @param  Patient  $patient
     * @param  array  $data
     * @return Patient
     */
    public function updatePatient(Patient $patient, array $data): Patient;

    /**
     * @param  Patient  $patient
     * @return void
     */
    public function deletePatient(Patient $patient): void;

    /**
     * @param  ExportRequest  $request
     * @return BinaryFileResponse
     *
     * @throws Exception
     * @throws WriterException
     */
    public function export(ExportRequest $request): BinaryFileResponse;
}
