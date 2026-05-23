<?php

namespace App\Exports;

use Illuminate\Support\Collection;

/**
 * Summary of JobPositionExport
 */
final class PatientExport extends Export
{
    public function __construct(
        private Collection $patients
    ) {}

    public function collection(): Collection
    {
        return $this->patients;
    }

    public function headings(): array
    {
        return [
            'Imię',
            'Nazwisko',
            'Email',
        ];
    }

    public function map(mixed $row): array
    {
        return [
            'first_name' => $row->first_name,
            'last_name' => $row->last_name,
            'email' => $row->email,
        ];
    }
}
