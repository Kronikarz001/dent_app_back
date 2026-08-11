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
            'Data',
            'Data zakończenia',
            'Godzina rozpoczęcia',
            'Godzina zakończenia',
            'Nie przyszedł',
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
            'date' => $row->date,
            'end_date' => $row->end_date,
            'start_time' => $row->start_time,
            'end_time' => $row->end_time,
            'no_show' => $row->no_show ? 'Tak' : 'Nie',
            'is_active' => $row->is_active ? 'Tak' : 'Nie',
        ];
    }
}
