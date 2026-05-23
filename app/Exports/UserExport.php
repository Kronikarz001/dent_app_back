<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Summary of UserExport
 */
final class UserExport extends Export
{
    /**
     * @param  Collection  $users
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
     * @param  User  $row
     * @return array
     */
    public function map(mixed $row): array
    {
        return [
            'first_name' => $row->first_name,
            'last_name' => $row->last_name,
            'email' => $row->email,
            'private_email' => $row->private_email,
            'pesel' => $row->pesel,
        ];
    }
}
