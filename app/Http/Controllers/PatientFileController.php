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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;

class PatientFileController extends Controller
{
    public function __construct(
        private readonly FileServiceInterface $fileService
    ) {}

    #[OA\Get(
        path: '/api/patient/{patient}/file',
        tags: ['PatientFile'],
        summary: 'Lista plików pacjenta (paginacja)',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'patient', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/FileResource')),
                        ]),
                    ]
                )
            ),
        ]
    )]
    public function index(Patient $patient): LengthAwarePaginator
    {
        return $this->fileService->getAllFiles($patient);
    }

    #[OA\Post(
        path: '/api/patient/{patient}/file',
        tags: ['PatientFile'],
        summary: 'Wgrywa pliki dla pacjenta',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'patient', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['files[]'],
                    properties: [
                        new OA\Property(property: 'files[]', type: 'array', items: new OA\Items(type: 'string', format: 'binary')),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/FileResource'))]
                )
            ),
        ]
    )]
    public function store(Patient $patient, FileStoreRequest $request): AnonymousResourceCollection
    {
        return FileResource::collection(
            $this->fileService->saveFile(
                new FileDto($request->file('files'), FileableType::PATIENT),
                $patient
            )
        );
    }

    #[OA\Get(
        path: '/api/patient/{patient}/file/{file}',
        tags: ['PatientFile'],
        summary: 'Pobiera metadane pliku pacjenta',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'patient', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\PathParameter(name: 'file', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/FileResource')]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function show(Patient $patient, File $file): FileResource
    {
        $this->assertFileBelongsTo($file, $patient);

        return new FileResource($file);
    }

    #[OA\Get(
        path: '/api/patient/{patient}/file-download/{file}',
        tags: ['PatientFile'],
        summary: 'Pobiera zawartość pliku pacjenta',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'patient', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\PathParameter(name: 'file', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function download(Patient $patient, File $file): JsonResponse
    {
        $this->assertFileBelongsTo($file, $patient);

        return new JsonResponse($this->fileService->getFile($file));
    }

    #[OA\Put(
        path: '/api/patient/{patient}/file/{file}',
        tags: ['PatientFile'],
        summary: 'Zmienia nazwę pliku pacjenta',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'patient', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\PathParameter(name: 'file', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['filename'],
                properties: [
                    new OA\Property(property: 'filename', type: 'string', example: 'nowa_nazwa'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/FileResource')]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function update(Patient $patient, File $file, FileUpdateRequest $request): FileResource
    {
        $this->assertFileBelongsTo($file, $patient);

        return new FileResource(
            $this->fileService->updateFileName($file, $request->input('filename'))
        );
    }

    #[OA\Delete(
        path: '/api/patient/{patient}/file/{file}',
        tags: ['PatientFile'],
        summary: 'Usuwa plik pacjenta',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'patient', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\PathParameter(name: 'file', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Usunięto'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function destroy(Patient $patient, File $file): JsonResponse
    {
        $this->assertFileBelongsTo($file, $patient);

        $this->fileService->deleteFile($file);

        return new JsonResponse(null, 204);
    }

    #[OA\Post(
        path: '/api/patient/{patient}/file-new-version/{file}',
        tags: ['PatientFile'],
        summary: 'Tworzy nową wersję pliku pacjenta',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'patient', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\PathParameter(name: 'file', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['files[]'],
                    properties: [
                        new OA\Property(property: 'files[]', type: 'array', items: new OA\Items(type: 'string', format: 'binary')),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/FileResource'))]
                )
            ),
        ]
    )]
    public function storeNewVersion(Patient $patient, File $file, FileStoreRequest $request): AnonymousResourceCollection
    {
        $this->assertFileBelongsTo($file, $patient);

        return FileResource::collection(
            $this->fileService->createNewVersionFile(
                $file,
                new FileDto($request->file('files'), FileableType::PATIENT),
                $patient
            )
        );
    }
}
