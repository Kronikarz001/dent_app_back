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
use Illuminate\Http\Response;
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
     * @return JsonResponse
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
        return response()->json([], 204);
    }

    /**
     * @throws FileNotFoundException
     */
    public function download(Patient $patient, File $file): Response
    {
        return response($this->fileService->getFileContent($file), 200, ['Content-Type' => $file->mimetype]);
    }

    /**
     * @param Patient $patient
     * @param File $file
     * @param FileStoreRequest $request
     * @return JsonResponse
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
