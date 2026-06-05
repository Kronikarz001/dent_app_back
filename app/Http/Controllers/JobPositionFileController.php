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
     * @OA\Get(
     *     path="/api/job-position/{jobPosition}/file",
     *     tags={"JobPositionFile"},
     *     summary="Lista plików stanowiska (paginacja)",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(name="jobPosition", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\Response(response=200, description="OK",
     *
     *         @OA\JsonContent(allOf={
     *
     *             @OA\Schema(ref="#/components/schemas/PaginatedResponse"),
     *             @OA\Schema(@OA\Property(property="data", type="array",
     *
     *                 @OA\Items(ref="#/components/schemas/FileResource")
     *             ))
     *         })
     *     )
     * )
     *
     * @param JobPosition $jobPosition
     * @return LengthAwarePaginator
     */
    public function index(JobPosition $jobPosition): LengthAwarePaginator
    {
        return $this->fileService->getAllFiles($jobPosition);
    }

    /**
     * @OA\Post(
     *     path="/api/job-position/{jobPosition}/file",
     *     tags={"JobPositionFile"},
     *     summary="Wgrywa pliki dla stanowiska",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(name="jobPosition", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *                 required={"files[]"},
     *
     *                 @OA\Property(property="files[]", type="array",
     *
     *                     @OA\Items(type="string", format="binary")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="OK",
     *
     *         @OA\JsonContent(@OA\Property(property="data", type="array",
     *
     *             @OA\Items(ref="#/components/schemas/FileResource")
     *         ))
     *     )
     * )
     *
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
     * @OA\Get(
     *     path="/api/job-position/{jobPosition}/file/{file}",
     *     tags={"JobPositionFile"},
     *     summary="Pobiera metadane pliku stanowiska",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(name="jobPosition", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="file", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\Response(response=200, description="OK",
     *
     *         @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/FileResource"))
     *     ),
     *
     *     @OA\Response(response=404, description="Nie znaleziono")
     * )
     *
     * @param JobPosition $jobPosition
     * @param File $file
     * @return FileResource
     */
    public function show(JobPosition $jobPosition, File $file): FileResource
    {
        return new FileResource($file);
    }

    /**
     * @OA\Get(
     *     path="/api/job-position/{jobPosition}/file-download/{file}",
     *     tags={"JobPositionFile"},
     *     summary="Pobiera zawartość pliku stanowiska",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(name="jobPosition", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="file", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=404, description="Nie znaleziono")
     * )
     *
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
     * @OA\Put(
     *     path="/api/job-position/{jobPosition}/file/{file}",
     *     tags={"JobPositionFile"},
     *     summary="Zmienia nazwę pliku stanowiska",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(name="jobPosition", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="file", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"filename"},
     *
     *             @OA\Property(property="filename", type="string", example="nowa_nazwa")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="OK",
     *
     *         @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/FileResource"))
     *     ),
     *
     *     @OA\Response(response=404, description="Nie znaleziono"),
     *     @OA\Response(response=422, description="Błąd walidacji")
     * )
     *
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
     * @OA\Delete(
     *     path="/api/job-position/{jobPosition}/file/{file}",
     *     tags={"JobPositionFile"},
     *     summary="Usuwa plik stanowiska",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(name="jobPosition", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="file", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\Response(response=204, description="Usunięto"),
     *     @OA\Response(response=404, description="Nie znaleziono")
     * )
     *
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
     * @OA\Post(
     *     path="/api/job-position/{jobPosition}/file-new-version/{file}",
     *     tags={"JobPositionFile"},
     *     summary="Tworzy nową wersję pliku stanowiska",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(name="jobPosition", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="file", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *                 required={"files[]"},
     *
     *                 @OA\Property(property="files[]", type="array",
     *
     *                     @OA\Items(type="string", format="binary")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="OK",
     *
     *         @OA\JsonContent(@OA\Property(property="data", type="array",
     *
     *             @OA\Items(ref="#/components/schemas/FileResource")
     *         ))
     *     )
     * )
     *
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
