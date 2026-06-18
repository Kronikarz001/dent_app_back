<?php

namespace App\Services;

use App\Http\Requests\ExportRequest;
use App\Models\DentalExamination;
use Illuminate\Pagination\LengthAwarePaginator;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Summary of DentalExaminationServiceInterface
 */
interface DentalExaminationServiceInterface
{
    /**
     * @return LengthAwarePaginator
     */
    public function getDentalExaminations(): LengthAwarePaginator;

    /**
     * @return LengthAwarePaginator
     */
    public function getDentalExaminationsList(): LengthAwarePaginator;

    /**
     * @param array $data
     * @return DentalExamination
     */
    public function createDentalExamination(array $data): DentalExamination;

    /**
     * @param DentalExamination $dentalExamination
     * @param array $data
     * @return DentalExamination
     */
    public function updateDentalExamination(DentalExamination $dentalExamination, array $data): DentalExamination;

    /**
     * @param DentalExamination $dentalExamination
     * @return void
     */
    public function deleteDentalExamination(DentalExamination $dentalExamination): void;

    /**
     * @param ExportRequest $request
     * @return BinaryFileResponse
     *
     * @throws Exception
     * @throws WriterException
     */
    public function export(ExportRequest $request): BinaryFileResponse;
}
