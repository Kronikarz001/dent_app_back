<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\UploadedFile;

interface UserAvatarServiceInterface
{
    /**
     * @param User $user
     * @param UploadedFile $file
     * @return void
     */
    public function saveAvatar(User $user, UploadedFile $file): void;

    /**
     * @param User $user
     * @return array{content: string, mimeType: string}
     * @throws FileNotFoundException
     */
    public function getAvatar(User $user): array;

    /**
     * @param User $user
     * @return void
     */
    public function deleteAvatar(User $user): void;
}
