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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;

class JobPositionFileController extends Controller
{
    public function __construct(
        private readonly FileServiceInterface $fileService
    ) {}

    #[OA\Get(
        path: '/api/job-position/{jobPosition}/file',
        tags: ['JobPositionFile'],
        summary: 'Lista plików stanowiska (paginacja)',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'jobPosition', schema: new OA\Schema(type: 'string', format: 'uuid')),
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
    public function index(JobPosition $jobPosition): LengthAwarePaginator
    {
        return $this->fileService->getAllFiles($jobPosition);
    }

    #[OA\Post(
        path: '/api/job-position/{jobPosition}/file',
        tags: ['JobPositionFile'],
        summary: 'Wgrywa pliki dla stanowiska',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'jobPosition', schema: new OA\Schema(type: 'string', format: 'uuid')),
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
    public function store(JobPosition $jobPosition, FileStoreRequest $request): AnonymousResourceCollection
    {
        return FileResource::collection(
            $this->fileService->saveFile(
                new FileDto($request->file('files'), FileableType::JOB_POSITION),
                $jobPosition
            )
        );
    }

    #[OA\Get(
        path: '/api/job-position/{jobPosition}/file/{file}',
        tags: ['JobPositionFile'],
        summary: 'Pobiera metadane pliku stanowiska',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'jobPosition', schema: new OA\Schema(type: 'string', format: 'uuid')),
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
    public function show(JobPosition $jobPosition, File $file): FileResource
    {
        $this->assertFileBelongsTo($file, $jobPosition);

        return new FileResource($file);
    }

    #[OA\Get(
        path: '/api/job-position/{jobPosition}/file-download/{file}',
        tags: ['JobPositionFile'],
        summary: 'Pobiera zawartość pliku stanowiska',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'jobPosition', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\PathParameter(name: 'file', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function download(JobPosition $jobPosition, File $file): JsonResponse
    {
        $this->assertFileBelongsTo($file, $jobPosition);

        return new JsonResponse($this->fileService->getFile($file));
    }

    #[OA\Put(
        path: '/api/job-position/{jobPosition}/file/{file}',
        tags: ['JobPositionFile'],
        summary: 'Zmienia nazwę pliku stanowiska',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'jobPosition', schema: new OA\Schema(type: 'string', format: 'uuid')),
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
    public function update(JobPosition $jobPosition, File $file, FileUpdateRequest $request): FileResource
    {
        $this->assertFileBelongsTo($file, $jobPosition);

        return new FileResource(
            $this->fileService->updateFileName($file, $request->input('filename'))
        );
    }

    #[OA\Delete(
        path: '/api/job-position/{jobPosition}/file/{file}',
        tags: ['JobPositionFile'],
        summary: 'Usuwa plik stanowiska',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'jobPosition', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\PathParameter(name: 'file', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Usunięto'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function destroy(JobPosition $jobPosition, File $file): JsonResponse
    {
        $this->assertFileBelongsTo($file, $jobPosition);

        $this->fileService->deleteFile($file);

        return new JsonResponse(null, 204);
    }

    #[OA\Post(
        path: '/api/job-position/{jobPosition}/file-new-version/{file}',
        tags: ['JobPositionFile'],
        summary: 'Tworzy nową wersję pliku stanowiska',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'jobPosition', schema: new OA\Schema(type: 'string', format: 'uuid')),
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
    public function storeNewVersion(JobPosition $jobPosition, File $file, FileStoreRequest $request): AnonymousResourceCollection
    {
        $this->assertFileBelongsTo($file, $jobPosition);

        return FileResource::collection(
            $this->fileService->createNewVersionFile(
                $file,
                new FileDto($request->file('files'), FileableType::JOB_POSITION),
                $jobPosition
            )
        );
    }
}
