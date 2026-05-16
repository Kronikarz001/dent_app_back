<?php

namespace App\Services;

use App\DTO\FileDto;
use App\Models\File;
use App\Models\UuidModel;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Summary of FileServiceInterface
 */
interface FileServiceInterface
{
    /**
     * @param FileDto $fileDto
     * @param UuidModel $model
     * @return array
     */
    public function saveFile(FileDto $fileDto, UuidModel $model): array;

    /**
     * @param UuidModel $model
     * @param array $excludeUuids
     * @return LengthAwarePaginator
     */
    public function getAllFiles(UuidModel $model, array $excludeUuids = []): LengthAwarePaginator;

    /**
     * @param UuidModel $model
     * @return Collection
     */
    public function getAllFilesWithoutPagination(UuidModel $model): Collection;

    /**
     * @param File $file
     * @return array
     * @throws FileNotFoundException
     */
    public function getFile(File $file): array;

    /**
     * @param File $file
     * @return bool
     */
    public function deleteFile(File $file): bool;

    /**
     * @param File $existingFile
     * @param FileDto $fileDto
     * @param UuidModel $model
     * @return array
     */
    public function createNewVersionFile(File $existingFile, FileDto $fileDto, UuidModel $model): array;

    /**
     * @param File $existingFile
     * @param string $newFilename
     * @return File
     */
    public function updateFileName(File $existingFile, string $newFilename): File;
}
