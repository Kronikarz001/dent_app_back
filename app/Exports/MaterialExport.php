<?php

namespace App\Exports;

use Illuminate\Support\Collection;

/**
 * Summary of MaterialExport
 */
final class MaterialExport extends Export
{
    /**
     * @param Collection $materials
     */
    public function __construct(
        private Collection $materials
    ) {}

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        return $this->materials;
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
