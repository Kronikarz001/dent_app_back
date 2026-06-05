<?php

namespace App\Http\Controllers;

use App\Dto\FileDto;
use App\Enums\FileableType;
use App\Http\Requests\FileStoreRequest;
use App\Http\Requests\FileUpdateRequest;
use App\Http\Resources\FileResource;
use App\Models\File;
use App\Models\User;
use App\Models\UserAvatar;
use App\Models\UserBackground;
use App\Services\FileServiceInterface;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of UserFileController
 */
class UserFileController extends Controller
{
    /**
     * @param FileServiceInterface $fileService
     */
    public function __construct(
        private readonly FileServiceInterface $fileService
    ) {}

    /**
     * @param User $user
     * @return LengthAwarePaginator
     */
    public function index(User $user): LengthAwarePaginator
    {
        return $this->fileService->getAllFiles($user);
    }

    /**
     * @param User $user
     * @param FileStoreRequest $request
     * @return AnonymousResourceCollection
     */
    public function store(User $user, FileStoreRequest $request): AnonymousResourceCollection
    {
        return FileResource::collection(
            $this->fileService->saveFile(
                new FileDto($request->file('files'), FileableType::USER),
                $user
            )
        );
    }

    /**
     * @param User $user
     * @param File $file
     * @return FileResource
     */
    public function show(User $user, File $file): FileResource
    {
        return new FileResource($file);
    }

    /**
     * @param User $user
     * @param File $file
     * @return JsonResponse
     *
     * @throws FileNotFoundException
     */
    public function download(User $user, File $file): JsonResponse
    {
        return new JsonResponse($this->fileService->getFile($file));
    }

    /**
     * @param User $user
     * @param File $file
     * @param FileUpdateRequest $request
     * @return FileResource
     */
    public function update(User $user, File $file, FileUpdateRequest $request): FileResource
    {
        return new FileResource(
            $this->fileService->updateFileName($file, $request->input('filename'))
        );
    }

    /**
     * @param User $user
     * @param File $file
     * @return JsonResponse
     */
    public function destroy(User $user, File $file): JsonResponse
    {
        $this->fileService->deleteFile($file);

        return new JsonResponse(null, 204);
    }

    /**
     * @param User $user
     * @param File $file
     * @param FileStoreRequest $request
     * @return AnonymousResourceCollection
     */
    public function storeNewVersion(User $user, File $file, FileStoreRequest $request): AnonymousResourceCollection
    {
        return FileResource::collection(
            $this->fileService->createNewVersionFile(
                $file,
                new FileDto($request->file('files'), FileableType::USER),
                $user
            )
        );
    }

    /**
     * @param User $user
     * @param FileStoreRequest $request
     * @return AnonymousResourceCollection
     */
    public function storeAvatar(User $user, FileStoreRequest $request): AnonymousResourceCollection
    {
        return FileResource::collection(
            $this->fileService->saveFile(
                new FileDto($request->file('files'), FileableType::USER_AVATAR),
                UserAvatar::find($user->uuid)
            )
        );
    }

    /**
     * @param User $user
     * @param File $file
     * @return Response
     *
     * @throws FileNotFoundException
     */
    public function avatarDownload(User $user, File $file): Response
    {
        return response($this->fileService->getPhotoFile($file), 200, ['Content-Type' => $file->mimetype]);
    }

    /**
     * @param User $user
     * @param FileStoreRequest $request
     * @return AnonymousResourceCollection
     */
    public function storeBackground(User $user, FileStoreRequest $request): AnonymousResourceCollection
    {
        return FileResource::collection(
            $this->fileService->saveFile(
                new FileDto($request->file('files'), FileableType::USER_BACKGROUND),
                UserBackground::find($user->uuid)
            )
        );
    }

    /**
     * @param User $user
     * @param File $file
     * @return Response
     *
     * @throws FileNotFoundException
     */
    public function backgroundDownload(User $user, File $file): Response
    {
        return response($this->fileService->getPhotoFile($file), 200, ['Content-Type' => $file->mimetype]);
    }
}
