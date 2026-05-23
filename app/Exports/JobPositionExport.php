<?php

namespace App\Exports;

use Illuminate\Support\Collection;

/**
 * Summary of JobPositionExport
 */
final class JobPositionExport extends Export
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
}
