<?php

namespace App\Services;

use App\Exceptions\FileUploadException;
use App\Models\Patient;
use App\Models\Photo;
use App\Repositories\PhotoRepositoryInterface;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Summary of PhotoService
 */
readonly class PhotoService implements PhotoServiceInterface
{
    /**
     * @param PhotoRepositoryInterface $photoRepository
     */
    public function __construct(
        private PhotoRepositoryInterface $photoRepository,
    ) {}

    /**
     * @param Patient $patient
     * @return LengthAwarePaginator
     */
    public function getAllPhotos(Patient $patient): LengthAwarePaginator
    {
        return $this->photoRepository->findAllWithPagination([
            'patient_uuid' => $patient->uuid,
        ]);
    }

    /**
     * @param UploadedFile[] $files
     * @param Patient $patient
     * @return Photo[]
     *
     * @throws FileUploadException
     */
    public function savePhotos(array $files, Patient $patient): array
    {
        return array_map(
            fn (UploadedFile $upload) => $this->processSinglePhoto($upload, $patient),
            $files
        );
    }

    /**
     * @param Photo $photo
     * @return array
     *
     * @throws FileNotFoundException
     */
    public function getPhoto(Photo $photo): array
    {
        $decrypted = $this->getPhotoContent($photo);

        return [
            'filename' => $photo->filename,
            'extension' => $photo->extension,
            'mime' => $photo->mimetype,
            'content' => base64_encode($decrypted),
        ];
    }

    /**
     * @param Photo $photo
     * @param string $newFilename
     * @return Photo
     */
    public function updatePhotoName(Photo $photo, string $newFilename): Photo
    {
        return $this->photoRepository->update($photo, [
            'filename' => $newFilename,
        ]);
    }

    /**
     * @param Photo $photo
     * @return bool
     *
     * @throws FileNotFoundException
     */
    public function deletePhoto(Photo $photo): bool
    {
        $this->deletePhysicalPhoto($photo);

        return $this->photoRepository->delete($photo);
    }

    /**
     * @param Photo $photo
     * @return string
     *
     * @throws FileNotFoundException
     */
    private function getPhotoContent(Photo $photo): string
    {
        if (! Storage::disk('files')->exists($photo->path)) {
            throw new FileNotFoundException($photo->path);
        }

        return Crypt::decrypt(Storage::disk('files')->get($photo->path));
    }

    /**
     * @param UploadedFile $upload
     * @param Patient $patient
     * @return Photo
     *
     * @throws FileUploadException
     */
    private function processSinglePhoto(UploadedFile $upload, Patient $patient): Photo
    {
        $newUuid = Str::uuid()->toString();
        $path = $this->makePhotoPath($newUuid, $patient->uuid);

        $this->savePhotoToDisk($upload, $path);

        return $this->photoRepository->create([
            'uuid' => $newUuid,
            'filename' => pathinfo($upload->getClientOriginalName(), PATHINFO_FILENAME),
            'patient_uuid' => $patient->uuid,
            'extension' => $upload->getClientOriginalExtension(),
            'size' => $upload->getSize(),
            'mimetype' => $upload->getMimeType(),
            'path' => $path,
        ]);
    }

    /**
     * @param UploadedFile $file
     * @param string $path
     * @return void
     *
     * @throws FileUploadException
     */
    private function savePhotoToDisk(UploadedFile $file, string $path): void
    {
        $encrypted = Crypt::encrypt($file->getContent());

        if (! Storage::disk('files')->put($path, $encrypted)) {
            throw new FileUploadException("Error uploading file to {$path}");
        }
    }

    /**
     * @param string $currentUuid
     * @param string $patientUuid
     * @return string
     */
    private function makePhotoPath(string $currentUuid, string $patientUuid): string
    {
        $segments = str_split(substr($patientUuid, 0, 6), 2);
        $uuidPath = implode('/', $segments).'/'.$patientUuid;

        return "photo/{$uuidPath}/{$currentUuid}";
    }

    /**
     * @param Photo $photo
     * @return void
     *
     * @throws FileNotFoundException
     */
    private function deletePhysicalPhoto(Photo $photo): void
    {
        if (! Storage::disk('files')->exists($photo->path)) {
            throw new FileNotFoundException($photo->path);
        }

        Storage::disk('files')->delete($photo->path);
    }
}
