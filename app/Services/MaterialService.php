<?php

namespace App\Services;

use App\Exports\MaterialExport;
use App\Http\Requests\ExportRequest;
use App\Models\Material;
use App\Repositories\MaterialRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Summary of MaterialService
 */
readonly class MaterialService implements MaterialServiceInterface
{
    /**
     * @param MaterialRepositoryInterface $materialRepository
     * @param ExportServiceInterface $exportService
     */
    public function __construct(
        private MaterialRepositoryInterface $materialRepository,
        private ExportServiceInterface $exportService,
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    public function getMaterials(): LengthAwarePaginator
    {
        return $this->materialRepository->findAllWithPagination();
    }

    /**
     * @return LengthAwarePaginator
     */
    public function getMaterialsList(): LengthAwarePaginator
    {
        return $this->materialRepository->findAllWithPagination(['uuid', 'name']);
    }

    /**
     * @param array $data
     * @return Material
     */
    public function createMaterial(array $data): Material
    {
        return $this->materialRepository->create($data);
    }

    /**
     * @param Material $material
     * @param array $data
     * @return Material
     */
    public function updateMaterial(Material $material, array $data): Material
    {
        return $this->materialRepository->update($material, $data);
    }

    /**
     * @param Material $material
     * @return void
     */
    public function deleteMaterial(Material $material): void
    {
        $this->materialRepository->delete($material);
    }

    /**
     * @param ExportRequest $request
     * @return BinaryFileResponse
     *
     * @throws Exception
     * @throws WriterException
     */
    public function export(ExportRequest $request): BinaryFileResponse
    {
        return $this->exportService->export($request, new MaterialExport($this->getMaterials()->getCollection()), Material::getModel());
    }
}
