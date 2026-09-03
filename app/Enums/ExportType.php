<?php

namespace App\Enums;

/**
 * Summary of ExportType
 */
enum ExportType: string
{
    case XLSX = 'xlsx';
    case PDF = 'pdf';
    case CSV = 'csv';
    case XLS = 'xls';

    /**
     * @return string
     */
    public function name(): string
    {
        return match ($this) {
            self::XLSX => 'xlsx',
            self::PDF => 'pdf',
            self::CSV => 'csv',
            self::XLS => 'xls',
        };
    }
}
