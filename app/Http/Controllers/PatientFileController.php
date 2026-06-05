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
     * @OA\Get(
     *     path="/api/patient/{patient}/file",
     *     tags={"PatientFile"},
     *     summary="Lista plików pacjenta (paginacja)",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(name="patient", in="path", required=true, @OA\Schema(type="string", format="uuid")),
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
     * @param Patient $patient
     * @return LengthAwarePaginator
     */
    public function index(Patient $patient): LengthAwarePaginator
    {
        return $this->fileService->getAllFiles($patient);
    }

    /**
     * @OA\Post(
     *     path="/api/patient/{patient}/file",
     *     tags={"PatientFile"},
     *     summary="Wgrywa pliki dla pacjenta",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(name="patient", in="path", required=true, @OA\Schema(type="string", format="uuid")),
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
     * @OA\Get(
     *     path="/api/patient/{patient}/file/{file}",
     *     tags={"PatientFile"},
     *     summary="Pobiera metadane pliku pacjenta",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(name="patient", in="path", required=true, @OA\Schema(type="string", format="uuid")),
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
     * @param Patient $patient
     * @param File $file
     * @return FileResource
     */
    public function show(Patient $patient, File $file): FileResource
    {
        return new FileResource($file);
    }

    /**
     * @OA\Get(
     *     path="/api/patient/{patient}/file-download/{file}",
     *     tags={"PatientFile"},
     *     summary="Pobiera zawartość pliku pacjenta",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(name="patient", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="file", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=404, description="Nie znaleziono")
     * )
     *
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
     * @OA\Put(
     *     path="/api/patient/{patient}/file/{file}",
     *     tags={"PatientFile"},
     *     summary="Zmienia nazwę pliku pacjenta",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(name="patient", in="path", required=true, @OA\Schema(type="string", format="uuid")),
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
     * @OA\Delete(
     *     path="/api/patient/{patient}/file/{file}",
     *     tags={"PatientFile"},
     *     summary="Usuwa plik pacjenta",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(name="patient", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="file", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\Response(response=204, description="Usunięto"),
     *     @OA\Response(response=404, description="Nie znaleziono")
     * )
     *
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
     * @OA\Post(
     *     path="/api/patient/{patient}/file-new-version/{file}",
     *     tags={"PatientFile"},
     *     summary="Tworzy nową wersję pliku pacjenta",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(name="patient", in="path", required=true, @OA\Schema(type="string", format="uuid")),
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
