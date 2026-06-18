<?php

namespace App\Exports;

use Illuminate\Support\Collection;

/**
 * Summary of AuditExport
 */
final class AuditExport extends Export
{
    /**
     * @param Collection $audits
     */
    public function __construct(
        private Collection $audits
    ) {}

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        return $this->audits;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Typ zmiany',
            'Użytkownik',
            'Zmiana z',
            'Zmiana na',
            'Data',
        ];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map(mixed $row): array
    {
        return [
            'type' => $row->type->name(),
            'user' => $row->user ? "{$row->user->first_name} {$row->user->last_name}" : null,
            'change_from' => $row->change_from ? json_encode($row->change_from) : null,
            'change_to' => $row->change_to ? json_encode($row->change_to) : null,
            'created_at' => $row->created_at,
        ];
    }
}
