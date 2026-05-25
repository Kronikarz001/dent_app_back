<?php

namespace App\Http\Controllers;

use App\Dto\FileDto;
use App\Enums\FileableType;
use App\Http\Requests\FileStoreRequest;
use App\Http\Requests\FileUpdateRequest;
use App\Http\Resources\FileResource;
use App\Models\File;
use App\Models\User;
use App\Services\FileServiceInterface;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
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
     * @return JsonResponse
     */
    public function store(User $user, FileStoreRequest $request): JsonResponse
    {
        $files = $this->fileService->saveFile(
            new FileDto($request->file('files'), FileableType::USER),
            $user
        );

        return response()->json(FileResource::collection($files), 201);
    }

    /**
     * @param User $user
     * @param File $file
     * @return JsonResponse
     *
     * @throws FileNotFoundException
     */
    public function show(User $user, File $file): JsonResponse
    {
        return response()->json($this->fileService->getFile($file));
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

        return response()->json([], 204);
    }

    /**
     * @param User $user
     * @param File $file
     * @return Response
     *
     * @throws FileNotFoundException
     */
    public function download(User $user, File $file): Response
    {
        return response($this->fileService->getFileContent($file), 200, ['Content-Type' => $file->mimetype]);
    }

    /**
     * @param User $user
     * @param File $file
     * @param FileStoreRequest $request
     * @return JsonResponse
     */
    public function storeNewVersion(User $user, File $file, FileStoreRequest $request): JsonResponse
    {
        $files = $this->fileService->createNewVersionFile(
            $file,
            new FileDto($request->file('files'), FileableType::USER),
            $user
        );

        return response()->json(FileResource::collection($files), 201);
    }
}
