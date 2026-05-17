<?php

namespace App\Http\Controllers;

use App\Http\Requests\AvatarStoreRequest;
use App\Models\User;
use App\Services\UserAvatarServiceInterface;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class UserAvatarFileController extends Controller
{
    public function __construct(
        private readonly UserAvatarServiceInterface $avatarService,
    ) {}

    /**
     * @param User $user
     * @param AvatarStoreRequest $request
     * @return JsonResponse
     */
    public function store(User $user, AvatarStoreRequest $request): JsonResponse
    {
        $this->avatarService->saveAvatar($user, $request->file('avatar'));

        return response()->json([], 201);
    }

    /**
     * @param User $user
     * @return Response
     */
    public function show(User $user): Response
    {
        try {
            $avatar = $this->avatarService->getAvatar($user);
        } catch (FileNotFoundException) {
            abort(404);
        }

        return response($avatar['content'], 200, ['Content-Type' => $avatar['mimeType']]);
    }

    /**
     * @param User $user
     * @return JsonResponse
     */
    public function destroy(User $user): JsonResponse
    {
        $this->avatarService->deleteAvatar($user);

        return response()->json([], 204);
    }
}
