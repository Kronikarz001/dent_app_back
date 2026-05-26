<?php

namespace App\Services;

use App\Exports\ExportInterface;
use App\Http\Requests\ExportRequest;
use App\Mappers\ExportMapper;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Summary of ExportService
 */
readonly class ExportService implements ExportServiceInterface
{
    /**
     * @param ExportMapper $exportExtensionMapper
     */
    public function __construct(
        private ExportMapper $exportExtensionMapper
    ) {}

    /**
     * @param ExportRequest $request
     * @param ExportInterface $export
     * @param Model $model
     * @return BinaryFileResponse
     *
     * @throws Exception
     * @throws WriterException
     */
    public function export(ExportRequest $request, ExportInterface $export, Model $model): BinaryFileResponse
    {
        $table = $model->getTable();
        $dto = $request->getDto();
        $dto->setPerPage(-1);

        $type = $request->type;

        return Excel::download(
            $export,
            ($request->name ?? $table).".{$type}",
            $this->exportExtensionMapper->map($type)
        );
    }
}
