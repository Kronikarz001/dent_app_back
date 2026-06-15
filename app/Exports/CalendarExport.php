<?php

namespace App\Exports;

use Illuminate\Support\Collection;

/**
 * Summary of CalendarExport
 */
final class CalendarExport extends Export
{
    /**
     * @param Collection $calendars
     */
    public function __construct(
        private Collection $calendars
    ) {}

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        return $this->calendars;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Nazwa',
            'Opis',
            'Typ',
            'Data początku',
            'Data końca',
            'Aktywny',
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
            'type' => $row->type,
            'start_date' => $row->start_date,
            'end_date' => $row->end_date,
            'is_active' => $row->is_active ? 'Tak' : 'Nie',
        ];
    }
}