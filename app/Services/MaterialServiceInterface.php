<?php

namespace App\Services;

use App\Http\Requests\ExportRequest;
use App\Models\Material;
use Illuminate\Pagination\LengthAwarePaginator;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Summary of MaterialServiceInterface
 */
interface MaterialServiceInterface
{
    /**
     * @return LengthAwarePaginator
     */
    public function getMaterials(): LengthAwarePaginator;

    /**
     * @return LengthAwarePaginator
     */
    public function getMaterialsList(): LengthAwarePaginator;

    /**
     * @param array $data
     * @return Material
     */
    public function createMaterial(array $data): Material;

    /**
     * @param Material $material
     * @param array $data
     * @return Material
     */
    public function updateMaterial(Material $material, array $data): Material;

    /**
     * @param Material $material
     * @return void
     */
    public function deleteMaterial(Material $material): void;

    /**
     * @param ExportRequest $request
     * @return BinaryFileResponse
     *
     * @throws Exception
     * @throws WriterException
     */
    public function export(ExportRequest $request): BinaryFileResponse;
}
