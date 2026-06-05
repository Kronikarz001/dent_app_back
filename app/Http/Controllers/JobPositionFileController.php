<?php

namespace App\Http\Controllers;

use App\Dto\FileDto;
use App\Enums\FileableType;
use App\Http\Requests\FileStoreRequest;
use App\Http\Requests\FileUpdateRequest;
use App\Http\Resources\FileResource;
use App\Models\File;
use App\Models\JobPosition;
use App\Services\FileServiceInterface;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of JobPositionFileController
 */
class JobPositionFileController extends Controller
{
    /**
     * @param FileServiceInterface $fileService
     */
    public function __construct(
        private readonly FileServiceInterface $fileService
    ) {}

    /**
     * @param JobPosition $jobPosition
     * @return LengthAwarePaginator
     */
    public function index(JobPosition $jobPosition): LengthAwarePaginator
    {
        return $this->fileService->getAllFiles($jobPosition);
    }

    /**
     * @param JobPosition $jobPosition
     * @param FileStoreRequest $request
     * @return AnonymousResourceCollection
     */
    public function store(JobPosition $jobPosition, FileStoreRequest $request): AnonymousResourceCollection
    {
        return FileResource::collection(
            $this->fileService->saveFile(
                new FileDto($request->file('files'), FileableType::JOB_POSITION),
                $jobPosition
            )
        );
    }

    /**
     * @param JobPosition $jobPosition
     * @param File $file
     * @return FileResource
     */
    public function show(JobPosition $jobPosition, File $file): FileResource
    {
        return new FileResource($file);
    }

    /**
     * @param JobPosition $jobPosition
     * @param File $file
     * @return JsonResponse
     *
     * @throws FileNotFoundException
     */
    public function download(JobPosition $jobPosition, File $file): JsonResponse
    {
        return new JsonResponse($this->fileService->getFile($file));
    }

    /**
     * @param JobPosition $jobPosition
     * @param File $file
     * @param FileUpdateRequest $request
     * @return FileResource
     */
    public function update(JobPosition $jobPosition, File $file, FileUpdateRequest $request): FileResource
    {
        return new FileResource(
            $this->fileService->updateFileName($file, $request->input('filename'))
        );
    }

    /**
     * @param JobPosition $jobPosition
     * @param File $file
     * @return JsonResponse
     */
    public function destroy(JobPosition $jobPosition, File $file): JsonResponse
    {
        $this->fileService->deleteFile($file);

        return new JsonResponse(null, 204);
    }

    /**
     * @param JobPosition $jobPosition
     * @param File $file
     * @param FileStoreRequest $request
     * @return AnonymousResourceCollection
     */
    public function storeNewVersion(JobPosition $jobPosition, File $file, FileStoreRequest $request): AnonymousResourceCollection
    {
        return FileResource::collection(
            $this->fileService->createNewVersionFile(
                $file,
                new FileDto($request->file('files'), FileableType::JOB_POSITION),
                $jobPosition
            )
        );
    }
}
