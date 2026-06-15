<?php

namespace App\Http\Controllers;

use App\Dto\FileDto;
use App\Enums\FileableType;
use App\Http\Requests\FileStoreRequest;
use App\Http\Requests\FileUpdateRequest;
use App\Http\Resources\FileResource;
use App\Models\Calendar;
use App\Models\File;
use App\Services\FileServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;

/**
 *
 */
class CalendarFileController extends Controller
{
    /**
     * @param FileServiceInterface $fileService
     */
    public function __construct(
        private readonly FileServiceInterface $fileService
    ) {}

    #[OA\Get(
        path: '/api/job-position/{calendar}/file',
        tags: ['CalendarFile'],
        summary: 'Lista plików stanowiska (paginacja)',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'calendar', schema: new OA\Schema(type: 'string', format: 'uuid')),
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
    /**
     *
     */
    public function index(Calendar $calendar): LengthAwarePaginator
    {
        return $this->fileService->getAllFiles($calendar);
    }

    #[OA\Post(
        path: '/api/job-position/{calendar}/file',
        tags: ['CalendarFile'],
        summary: 'Wgrywa pliki dla stanowiska',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'calendar', schema: new OA\Schema(type: 'string', format: 'uuid')),
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
    public function store(Calendar $calendar, FileStoreRequest $request): AnonymousResourceCollection
    {
        return FileResource::collection(
            $this->fileService->saveFile(
                new FileDto($request->file('files'), FileableType::JOB_POSITION),
                $calendar
            )
        );
    }

    #[OA\Get(
        path: '/api/job-position/{calendar}/file/{file}',
        tags: ['CalendarFile'],
        summary: 'Pobiera metadane pliku stanowiska',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'calendar', schema: new OA\Schema(type: 'string', format: 'uuid')),
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
    public function show(Calendar $calendar, File $file): FileResource
    {
        return new FileResource($file);
    }

    #[OA\Get(
        path: '/api/job-position/{calendar}/file-download/{file}',
        tags: ['CalendarFile'],
        summary: 'Pobiera zawartość pliku stanowiska',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'calendar', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\PathParameter(name: 'file', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function download(Calendar $calendar, File $file): JsonResponse
    {
        return new JsonResponse($this->fileService->getFile($file));
    }

    #[OA\Put(
        path: '/api/job-position/{calendar}/file/{file}',
        tags: ['CalendarFile'],
        summary: 'Zmienia nazwę pliku stanowiska',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'calendar', schema: new OA\Schema(type: 'string', format: 'uuid')),
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
    public function update(Calendar $calendar, File $file, FileUpdateRequest $request): FileResource
    {
        return new FileResource(
            $this->fileService->updateFileName($file, $request->input('filename'))
        );
    }

    #[OA\Delete(
        path: '/api/job-position/{calendar}/file/{file}',
        tags: ['CalendarFile'],
        summary: 'Usuwa plik stanowiska',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'calendar', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\PathParameter(name: 'file', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Usunięto'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function destroy(Calendar $calendar, File $file): JsonResponse
    {
        $this->fileService->deleteFile($file);

        return new JsonResponse(null, 204);
    }

    #[OA\Post(
        path: '/api/job-position/{calendar}/file-new-version/{file}',
        tags: ['CalendarFile'],
        summary: 'Tworzy nową wersję pliku stanowiska',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'calendar', schema: new OA\Schema(type: 'string', format: 'uuid')),
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
    public function storeNewVersion(Calendar $calendar, File $file, FileStoreRequest $request): AnonymousResourceCollection
    {
        return FileResource::collection(
            $this->fileService->createNewVersionFile(
                $file,
                new FileDto($request->file('files'), FileableType::JOB_POSITION),
                $calendar
            )
        );
    }
}
