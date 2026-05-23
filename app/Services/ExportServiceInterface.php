<?php

namespace App\Services;

use App\Exports\ExportInterface;
use App\Http\Requests\ExportRequest;
use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Summary of ExportServiceInterface
 */
interface ExportServiceInterface
{
    /**
     * @throws Exception
     * @throws WriterException
     */
    public function export(ExportRequest $request, ExportInterface $export, Model $model): BinaryFileResponse;
}
