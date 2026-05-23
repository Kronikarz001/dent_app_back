<?php

namespace App\Mappers;

use Maatwebsite\Excel\Excel as ExcelTypes;

/**
 * Summary of ExportMapper
 */
class ExportMapper implements MapperInterface
{
    /**
     * @const array<string>
     */
    private const EXTENSION_MAP = [
        'xlsx' => ExcelTypes::XLSX,
        'csv' => ExcelTypes::CSV,
        'pdf' => ExcelTypes::MPDF,
    ];

    /**
     * @return mixed
     */
    public function map(mixed $data): string
    {
        return self::EXTENSION_MAP[$data];
    }
}
