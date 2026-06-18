<?php

namespace App\Exports;

use Illuminate\Support\Collection;

/**
 * Summary of DentalExaminationExport
 */
final class DentalExaminationExport extends Export
{
    /**
     * @param Collection $dentalExaminations
     */
    public function __construct(
        private Collection $dentalExaminations
    ) {}

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        return $this->dentalExaminations;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Nazwa',
            'Opis',
            'Krótki opis',
            'Cena',
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
            'description' => $row->description,
            'short_description' => $row->short_description,
            'price' => $row->price,
        ];
    }
}
