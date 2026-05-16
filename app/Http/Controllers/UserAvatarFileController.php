<?php

namespace App\Http\Controllers;

use App\Http\Requests\AvatarStoreRequest;
use App\Models\UserAvatar;
use App\Models\User;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class UserAvatarFileController extends Controller
{
    private function disk(): \Illuminate\Contracts\Filesystem\Filesystem
    {
        return Storage::disk('local');
    }

    private function avatarPath(User $user): string
    {
        return 'avatar/' . $user->uuid;
    }

    /**
     * @param User $user
     * @param AvatarStoreRequest $request
     * @return JsonResponse
     */
    public function store(User $user, AvatarStoreRequest $request): JsonResponse
    {
        $disk = $this->disk();
        $path = $this->avatarPath($user);

        if ($user->avatar_path && $disk->exists($user->avatar_path)) {
            $disk->delete($user->avatar_path);
        }

        $upload = $request->file('avatar');
        $disk->put($path, Crypt::encrypt($upload->getContent()));

        UserAvatar::where('uuid', $user->uuid)->update(['avatar_path' => $path]);

        return response()->json(['avatar_path' => $path], 201);
    }

    /**
     * @param User $user
     * @return Response
     * @throws FileNotFoundException
     */
    public function show(User $user): Response
    {
        if (!$user->avatar_path) {
            abort(404);
        }

        $disk = $this->disk();

        if (!$disk->exists($user->avatar_path)) {
            throw new FileNotFoundException($user->avatar_path);
        }

        $decrypted = Crypt::decrypt($disk->get($user->avatar_path));
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($decrypted) ?: 'application/octet-stream';

        return response($decrypted, 200, ['Content-Type' => $mimeType]);
    }

    /**
     * @param User $user
     * @return JsonResponse
     */
    public function destroy(User $user): JsonResponse
    {
        if ($user->avatar_path) {
            $disk = $this->disk();

            if ($disk->exists($user->avatar_path)) {
                $disk->delete($user->avatar_path);
            }

            UserAvatar::where('uuid', $user->uuid)->update(['avatar_path' => null]);
        }

        return response()->json([], 204);
    }
}
