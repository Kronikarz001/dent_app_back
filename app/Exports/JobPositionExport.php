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
 * Summary of JobPositionExport
 */
final class JobPositionExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    /**
     * @param Collection $jobPositions
     */
    public function __construct(
        private Collection $jobPositions
    ) {}

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        return $this->jobPositions;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Nazwa stanowiska',
            'Nazwa r.żeński',
            'Nazwa r.męski',
        ];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map(mixed $row): array
    {
        return [
            'name' => $row->name,
            'f_name' => $row->f_name,
            'm_name' => $row->m_name,
        ];
    }

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
