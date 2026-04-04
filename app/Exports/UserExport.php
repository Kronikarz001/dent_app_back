<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

/**
 * Summary of UserExport
 */
final class UserExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    /**
     * @param Collection $users
     */
    public function __construct(
        private Collection $users
    ) {}

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        return $this->users;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Imię',
            'Nazwisko',
            'Email',
            'Email prywatny',
            'PESEL',
        ];
    }

    /**
     * @param User $row
     * @return array
     */
    public function map(mixed $row): array
    {
        return [
            'first_name'    => $row->first_name,
            'last_name'     => $row->last_name,
            'email'         => $row->email,
            'private_email' => $row->private_email,
            'pesel'         => $row->pesel,
        ];
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet         = $event->sheet->getDelegate();
                $highestRow    = $sheet->getHighestRow();
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
