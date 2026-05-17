<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserAvatar;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;

/**
 * Summary of UserAvatarService
 */
readonly class UserAvatarService implements UserAvatarServiceInterface
{
    /**
     * @param Filesystem $disk
     */
    public function __construct(
        private Filesystem $disk,
    ) {}

    /**
     * @param User $user
     * @param UploadedFile $file
     * @return void
     */
    public function saveAvatar(User $user, UploadedFile $file): void
    {
        if ($user->avatar_path && $this->disk->exists($user->avatar_path)) {
            $this->disk->delete($user->avatar_path);
        }

        $path = 'avatar/' . $user->uuid;

        $this->disk->put($path, Crypt::encrypt($file->getContent()));

        UserAvatar::where('uuid', $user->uuid)->update(['avatar_path' => $path]);
    }

    /**
     * @param User $user
     * @return array{content: string, mimeType: string}
     * @throws FileNotFoundException
     */
    public function getAvatar(User $user): array
    {
        if (!$user->avatar_path || !$this->disk->exists($user->avatar_path)) {
            throw new FileNotFoundException($user->avatar_path ?? '');
        }

        $content  = Crypt::decrypt($this->disk->get($user->avatar_path));
        $imageInfo = getimagesizefromstring($content);
        $mimeType  = $imageInfo['mime'] ?? 'application/octet-stream';

        return compact('content', 'mimeType');
    }

    /**
     * @param User $user
     * @return void
     */
    public function deleteAvatar(User $user): void
    {
        if (!$user->avatar_path) {
            return;
        }

        if ($this->disk->exists($user->avatar_path)) {
            $this->disk->delete($user->avatar_path);
        }

        UserAvatar::where('uuid', $user->uuid)->update(['avatar_path' => null]);
    }
}
