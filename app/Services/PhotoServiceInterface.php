<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Photo;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of PhotoServiceInterface
 */
interface PhotoServiceInterface
{
    /**
     * @param Patient $patient
     * @return LengthAwarePaginator
     */
    public function getAllPhotos(Patient $patient): LengthAwarePaginator;

    /**
     * @param UploadedFile[] $files
     * @param Patient $patient
     * @return Photo[]
     */
    public function savePhotos(array $files, Patient $patient): array;

    /**
     * @param Photo $photo
     * @return array
     *
     * @throws FileNotFoundException
     */
    public function getPhoto(Photo $photo): array;

    /**
     * @param Photo $photo
     * @param string $newFilename
     * @return Photo
     */
    public function updatePhotoName(Photo $photo, string $newFilename): Photo;

    /**
     * @param Photo $photo
     * @return bool
     *
     * @throws FileNotFoundException
     */
    public function deletePhoto(Photo $photo): bool;
}
