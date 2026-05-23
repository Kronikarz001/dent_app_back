<?php

namespace App\Exports;

use Illuminate\Support\Collection;

/**
 * Summary of JobPositionExport
 */
final class JobPositionExport extends Export
{
    public function __construct(
        private Collection $jobPositions
    ) {}

    public function collection(): Collection
    {
        return $this->jobPositions;
    }

    public function headings(): array
    {
        return [
            'Nazwa stanowiska',
            'Nazwa r.żeński',
            'Nazwa r.męski',
        ];
    }

    public function map(mixed $row): array
    {
        return [
            'name' => $row->name,
            'f_name' => $row->f_name,
            'm_name' => $row->m_name,
        ];
    }
}
