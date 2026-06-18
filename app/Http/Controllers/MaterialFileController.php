<?php

namespace App\Http\Controllers;

use App\Dto\FileDto;
use App\Enums\FileableType;
use App\Http\Requests\FileStoreRequest;
use App\Http\Requests\FileUpdateRequest;
use App\Http\Resources\FileResource;
use App\Models\File;
use App\Models\Material;
use App\Services\FileServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;

/**
 * Summary of MaterialFileController
 */
class MaterialFileController extends Controller
{
    /**
     * @param FileServiceInterface $fileService
     */
    public function __construct(
        private readonly FileServiceInterface $fileService
    ) {}

    #[OA\Get(
        path: '/api/material/{material}/file',
        tags: ['MaterialFile'],
        summary: 'Lista plików materiału (paginacja)',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'material', schema: new OA\Schema(type: 'string', format: 'uuid')),
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
    public function index(Material $material): LengthAwarePaginator
    {
        return $this->fileService->getAllFiles($material);
    }

    #[OA\Post(
        path: '/api/material/{material}/file',
        tags: ['MaterialFile'],
        summary: 'Wgrywa pliki dla materiału',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'material', schema: new OA\Schema(type: 'string', format: 'uuid')),
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
    public function store(Material $material, FileStoreRequest $request): AnonymousResourceCollection
    {
        return FileResource::collection(
            $this->fileService->saveFile(
                new FileDto($request->file('files'), FileableType::MATERIAL),
                $material
            )
        );
    }

    #[OA\Get(
        path: '/api/material/{material}/file/{file}',
        tags: ['MaterialFile'],
        summary: 'Pobiera metadane pliku materiału',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'material', schema: new OA\Schema(type: 'string', format: 'uuid')),
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
    public function show(Material $material, File $file): FileResource
    {
        return new FileResource($file);
    }

    #[OA\Get(
        path: '/api/material/{material}/file-download/{file}',
        tags: ['MaterialFile'],
        summary: 'Pobiera zawartość pliku materiału',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'material', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\PathParameter(name: 'file', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function download(Material $material, File $file): JsonResponse
    {
        return new JsonResponse($this->fileService->getFile($file));
    }

    #[OA\Put(
        path: '/api/material/{material}/file/{file}',
        tags: ['MaterialFile'],
        summary: 'Zmienia nazwę pliku materiału',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'material', schema: new OA\Schema(type: 'string', format: 'uuid')),
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
    public function update(Material $material, File $file, FileUpdateRequest $request): FileResource
    {
        return new FileResource(
            $this->fileService->updateFileName($file, $request->input('filename'))
        );
    }

    #[OA\Delete(
        path: '/api/material/{material}/file/{file}',
        tags: ['MaterialFile'],
        summary: 'Usuwa plik materiału',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'material', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\PathParameter(name: 'file', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Usunięto'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function destroy(Material $material, File $file): JsonResponse
    {
        $this->fileService->deleteFile($file);

        return new JsonResponse(null, 204);
    }

    #[OA\Post(
        path: '/api/material/{material}/file-new-version/{file}',
        tags: ['MaterialFile'],
        summary: 'Tworzy nową wersję pliku materiału',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'material', schema: new OA\Schema(type: 'string', format: 'uuid')),
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
    public function storeNewVersion(Material $material, File $file, FileStoreRequest $request): AnonymousResourceCollection
    {
        return FileResource::collection(
            $this->fileService->createNewVersionFile(
                $file,
                new FileDto($request->file('files'), FileableType::MATERIAL),
                $material
            )
        );
    }
}
