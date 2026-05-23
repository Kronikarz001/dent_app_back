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
    public function getPatients(): LengthAwarePaginator;

    public function getPatientsList(): LengthAwarePaginator;

    public function createPatient(array $data): Patient;

    public function updatePatient(Patient $patient, array $data): Patient;

    public function deletePatient(Patient $patient): void;

    /**
     * @throws Exception
     * @throws WriterException
     */
    public function export(ExportRequest $request): BinaryFileResponse;
}
