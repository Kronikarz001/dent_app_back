<?php

namespace App\Http\Controllers;

use App\Dto\FileDto;
use App\Enums\FileableType;
use App\Http\Requests\FileStoreRequest;
use App\Http\Requests\FileUpdateRequest;
use App\Http\Resources\FileResource;
use App\Models\File;
use App\Models\Patient;
use App\Services\FileServiceInterface;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of PatientFileController
 */
class PatientFileController extends Controller
{
    /**
     * @param FileServiceInterface $fileService
     */
    public function __construct(
        private readonly FileServiceInterface $fileService
    ) {}

    /**
     * @param Patient $patient
     * @return LengthAwarePaginator
     */
    public function index(Patient $patient): LengthAwarePaginator
    {
        return $this->fileService->getAllFiles($patient);
    }

    /**
     * @param Patient $patient
     * @param FileStoreRequest $request
     * @return AnonymousResourceCollection
     */
    public function store(Patient $patient, FileStoreRequest $request): AnonymousResourceCollection
    {
        return FileResource::collection(
            $this->fileService->saveFile(
                new FileDto($request->file('files'), FileableType::PATIENT),
                $patient
            )
        );
    }

    /**
     * @param Patient $patient
     * @param File $file
     * @return FileResource
     */
    public function show(Patient $patient, File $file): FileResource
    {
        return new FileResource($file);
    }

    /**
     * @param Patient $patient
     * @param File $file
     * @return JsonResponse
     *
     * @throws FileNotFoundException
     */
    public function download(Patient $patient, File $file): JsonResponse
    {
        return new JsonResponse($this->fileService->getFile($file));
    }

    /**
     * @param Patient $patient
     * @param File $file
     * @param FileUpdateRequest $request
     * @return FileResource
     */
    public function update(Patient $patient, File $file, FileUpdateRequest $request): FileResource
    {
        return new FileResource(
            $this->fileService->updateFileName($file, $request->input('filename'))
        );
    }

    /**
     * @param Patient $patient
     * @param File $file
     * @return JsonResponse
     */
    public function destroy(Patient $patient, File $file): JsonResponse
    {
        $this->fileService->deleteFile($file);

        return new JsonResponse(null, 204);
    }

    /**
     * @param Patient $patient
     * @param File $file
     * @param FileStoreRequest $request
     * @return AnonymousResourceCollection
     */
    public function storeNewVersion(Patient $patient, File $file, FileStoreRequest $request): AnonymousResourceCollection
    {
        return FileResource::collection(
            $this->fileService->createNewVersionFile(
                $file,
                new FileDto($request->file('files'), FileableType::PATIENT),
                $patient
            )
        );
    }
}
