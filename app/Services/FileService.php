<?php

namespace App\Services;

use App\DTO\FileDto;
use App\Enums\FileableType;
use App\Exceptions\FileUploadException;
use App\Models\File;
use App\Repositories\FileRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Summary of FileService
 */
readonly class FileService implements FileServiceInterface
{
    /**
     * @param FileRepositoryInterface $fileRepository
     */
    public function __construct(
        private FileRepositoryInterface $fileRepository,
    ) {}

    /**
     * @param Model $model
     * @param array $excludeUuids
     * @return LengthAwarePaginator
     */
    public function getAllFiles(Model $model, array $excludeUuids = []): LengthAwarePaginator
    {
        $excludeUuids = array_values(array_filter($excludeUuids, static fn($uuid) => !is_null($uuid)));

        return $this->fileRepository->findAllWithPagination([
            'fileable_type' => $model::class,
            'fileable_id' => $model->uuid,
            'is_latest' => true,
            'exclude_uuids' => $excludeUuids,
        ]);
    }

    /**
     * @param FileDto $fileDto
     * @param Model $model
     * @return array
     * @throws FileUploadException
     */
    public function saveFile(FileDto $fileDto, Model $model): array
    {
        return $this->processFiles($fileDto, $model);
    }

    /**
     * @param File $existingFile
     * @param FileDto $fileDto
     * @param Model $model
     * @return array
     * @throws FileUploadException
     */
    public function createNewVersionFile(File $existingFile, FileDto $fileDto, Model $model): array
    {
        return $this->processFiles($fileDto, $model, $existingFile);
    }

    /**
     * @param File $existingFile
     * @param string $newFilename
     * @return File
     */
    public function updateFileName(File $existingFile, string $newFilename): File
    {
        return $this->fileRepository->update($existingFile, [
            'filename' => $newFilename,
        ]);
    }

    /**
     * @param File $file
     * @return bool
     * @throws FileNotFoundException
     */
    public function deleteFile(File $file): bool
    {
        $file->files->each(fn(File $child) => $this->deletePhysicalFile($child));
        $this->deletePhysicalFile($file);
        return $this->fileRepository->delete($file);
    }

    /**
     * @param File $file
     * @return array
     * @throws FileNotFoundException
     */
    public function getFile(File $file): array
    {
        $decrypted = $this->getFileContent($file);

        return [
            'filename'  => $file->filename,
            'extension' => $file->extension,
            'mime'      => $file->mimetype,
            'content'   => base64_encode($decrypted),
        ];
    }

    /**
     * @param File $file
     * @return string
     * @throws FileNotFoundException
     */
    public function getFileContent(File $file): string
    {
        if (!Storage::disk('files')->exists($file->path)) {
            throw new FileNotFoundException($file->path);
        }

        return Crypt::decrypt(Storage::disk('files')->get($file->path));
    }

    /**
     * @param FileDto $fileDto
     * @param Model $model
     * @param File|null $parentFile
     * @return array
     * @throws FileUploadException
     */
    private function processFiles(FileDto $fileDto, Model $model, ?File $parentFile = null): array
    {
        $results = [];

        foreach ($fileDto->getFile() ?? [] as $upload) {
            $results[] = $this->processSingleFile(
                $upload,
                $fileDto->getType(),
                $model,
                $parentFile
            );
        }

        return $results;
    }

    /**
     * @param UploadedFile $upload
     * @param FileableType $type
     * @param Model $model
     * @param File|null $parentFile
     * @return File
     * @throws FileUploadException
     */
    private function processSingleFile(UploadedFile $upload, FileableType $type, Model $model, ?File $parentFile = null): File
    {
        $newUuid = Str::uuid()->toString();
        $rootUuid = $parentFile
            ? ($this->getRootUuidFromPath($parentFile->path, $type)
                ?? $this->fileRepository->getRootUuid($parentFile->fileable_type, $parentFile->fileable_id))
            : $newUuid;

        $path = $this->makeFilePath($newUuid, $type, $rootUuid);
        $this->saveFileToDisk($upload, $path);

        $newFile = $this->fileRepository->create([
            'uuid' => $newUuid,
            'filename' => pathinfo($upload->getClientOriginalName(), PATHINFO_FILENAME),
            'fileable_type' => $model->getMorphClass(),
            'fileable_id' => $model->uuid,
            'extension' => $upload->getClientOriginalExtension(),
            'size' => $upload->getSize(),
            'mimetype' => $upload->getMimeType(),
            'path' => $path,
            'file_uuid' => null,
            'is_latest' => true,
        ]);

        if ($parentFile) {
            $this->updateAllPreviousVersions($newFile, $parentFile);
        }

        return $newFile;
    }

    /**
     * @param UploadedFile $file
     * @param string $path
     * @return void
     * @throws FileUploadException
     */
    private function saveFileToDisk(UploadedFile $file, string $path): void
    {
        $encrypted = Crypt::encrypt($file->getContent());

        if (!Storage::disk('files')->put($path, $encrypted)) {
            throw new FileUploadException("Error uploading file to {$path}");
        }
    }

    /**
     * @param File $newFile
     * @param File|null $parentFile
     * @return void
     */
    private function updateAllPreviousVersions(File $newFile, ?File $parentFile): void
    {
        $previous = $this->fileRepository
            ->findAllByFileableExceptUuid(
                $newFile->fileable_type,
                $newFile->fileable_id,
                $newFile->uuid,
                $parentFile?->uuid,
            );

        /** @var File $old */
        foreach ($previous as $old) {
            $this->fileRepository->update($old, [
                'is_latest' => false,
                'file_uuid' => $newFile->uuid,
            ]);
        }
    }

    /**
     * @param File $file
     * @return void
     * @throws FileNotFoundException
     */
    private function deletePhysicalFile(File $file): void
    {
        if (!Storage::disk('files')->exists($file->path)) {
            throw new FileNotFoundException($file->path);
        }

        Storage::disk('files')->delete($file->path);
    }

    /**
     * @param string $currentUuid
     * @param FileableType $type
     * @param string $rootUuid
     * @return string
     */
    private function makeFilePath(string $currentUuid, FileableType $type, string $rootUuid): string
    {
        $segments = str_split(substr($rootUuid, 0, 6), 2);
        $uuidPath = implode('/', $segments) . '/' . $rootUuid;

        return "{$type->getDefaultDirectory()}/{$uuidPath}/{$currentUuid}";
    }

    /**
     * @param string $path
     * @param FileableType $type
     * @return string|null
     */
    private function getRootUuidFromPath(string $path, FileableType $type): ?string
    {
        $prefix = $type->getDefaultDirectory() . '/';
        if (!str_starts_with($path, $prefix)) {
            return null;
        }

        $parts = explode('/', substr($path, strlen($prefix)));
        return $parts[3] ?? null;
    }

    /**
     * @param Model $model
     * @return Collection
     */
    public function getAllFilesWithoutPagination(Model $model): Collection
    {
        return $this->fileRepository->findAllWithParams(
            [
                'fileable_type' => $model::class,
                'fileable_id' => $model->uuid,
                'is_latest' => true,
            ]
        );
    }
}
