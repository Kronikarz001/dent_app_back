<?php

namespace App\Http\Controllers;

use App\Dto\FileDto;
use App\Enums\FileableType;
use App\Exceptions\FileUploadException;
use App\Http\Requests\FileStoreRequest;
use App\Http\Requests\FileUpdateRequest;
use App\Http\Resources\FileResource;
use App\Models\File;
use App\Models\Patient;
use App\Services\FileServiceInterface;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class PatientFileController extends Controller
{
    public function __construct(
        private readonly FileServiceInterface $fileService
    ) {}

    public function index(Patient $patient): LengthAwarePaginator
    {
        return $this->fileService->getAllFiles($patient);
    }

    /**
     * @throws FileUploadException
     */
    public function store(Patient $patient, FileStoreRequest $request): JsonResponse
    {
        $files = $this->fileService->saveFile(
            new FileDto($request->file('files'), FileableType::PATIENT),
            $patient
        );

        return response()->json(FileResource::collection($files), 201);
    }

    /**
     * @throws FileNotFoundException
     */
    public function show(Patient $patient, File $file): JsonResponse
    {
        return response()->json($this->fileService->getFile($file));
    }

    public function update(Patient $patient, File $file, FileUpdateRequest $request): FileResource
    {
        return new FileResource(
            $this->fileService->updateFileName($file, $request->input('filename'))
        );
    }

    /**
     * @throws FileNotFoundException
     */
    public function destroy(Patient $patient, File $file): JsonResponse
    {
        $this->fileService->deleteFile($file);
        return response()->json([], 204);
    }

    /**
     * @throws FileNotFoundException
     */
    public function download(Patient $patient, File $file): JsonResponse
    {
        return response()->json($this->fileService->getFile($file));
    }

    /**
     * @throws FileUploadException
     */
    public function storeNewVersion(Patient $patient, File $file, FileStoreRequest $request): JsonResponse
    {
        $files = $this->fileService->createNewVersionFile(
            $file,
            new FileDto($request->file('files'), FileableType::PATIENT),
            $patient
        );

        return response()->json(FileResource::collection($files), 201);
    }
}
