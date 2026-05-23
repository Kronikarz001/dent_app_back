<?php

namespace App\Exports;

use Illuminate\Support\Collection;

/**
 * Summary of JobPositionExport
 */
final class PatientExport extends Export
{
    /**
     * @param Collection $patients
     */
    public function __construct(
        private Collection $patients
    ) {}

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        return $this->patients;
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
        ];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map(mixed $row): array
    {
        return [
            'first_name' => $row->first_name,
            'last_name' => $row->last_name,
            'email' => $row->email,
        ];
    }
}
