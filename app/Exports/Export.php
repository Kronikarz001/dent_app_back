<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

/**
 * Base for concrete exports (UserExport, PatientExport, ...) — collection(),
 * headings() and map() are left abstract so every subclass is forced by PHP
 * to provide them; only the shared border-styling event handler lives here.
 */
abstract class Export implements ExportInterface, FromCollection, WithEvents, WithHeadings, WithMapping
{
    /**
     * @return Collection
     */
    abstract public function collection();

    /**
     * @return array
     */
    abstract public function headings(): array;

    /**
     * @param mixed $row
     * @return array
     */
    abstract public function map($row): array;

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                $range = "A1:{$highestColumn}{$highestRow}";

                $sheet->getStyle($range)
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
            },
        ];
    }
}
