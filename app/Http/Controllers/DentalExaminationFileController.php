<?php

namespace App\Http\Controllers;

use App\Dto\FileDto;
use App\Enums\FileableType;
use App\Http\Requests\FileStoreRequest;
use App\Http\Requests\FileUpdateRequest;
use App\Http\Resources\FileResource;
use App\Models\DentalExamination;
use App\Models\File;
use App\Services\FileServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;

/**
 * Summary of DentalExaminationFileController
 */
class DentalExaminationFileController extends Controller
{
    /**
     * @param FileServiceInterface $fileService
     */
    public function __construct(
        private readonly FileServiceInterface $fileService
    ) {}

    #[OA\Get(
        path: '/api/dental-examination/{dentalExamination}/file',
        tags: ['DentalExaminationFile'],
        summary: 'Lista plików badania stomatologicznego (paginacja)',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'dentalExamination', schema: new OA\Schema(type: 'string', format: 'uuid')),
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
    public function index(DentalExamination $dentalExamination): LengthAwarePaginator
    {
        return $this->fileService->getAllFiles($dentalExamination);
    }

    #[OA\Post(
        path: '/api/dental-examination/{dentalExamination}/file',
        tags: ['DentalExaminationFile'],
        summary: 'Wgrywa pliki dla badania stomatologicznego',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'dentalExamination', schema: new OA\Schema(type: 'string', format: 'uuid')),
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
    public function store(DentalExamination $dentalExamination, FileStoreRequest $request): AnonymousResourceCollection
    {
        return FileResource::collection(
            $this->fileService->saveFile(
                new FileDto($request->file('files'), FileableType::DENTAL_EXAMINATION),
                $dentalExamination
            )
        );
    }

    #[OA\Get(
        path: '/api/dental-examination/{dentalExamination}/file/{file}',
        tags: ['DentalExaminationFile'],
        summary: 'Pobiera metadane pliku badania stomatologicznego',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'dentalExamination', schema: new OA\Schema(type: 'string', format: 'uuid')),
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
    public function show(DentalExamination $dentalExamination, File $file): FileResource
    {
        return new FileResource($file);
    }

    #[OA\Get(
        path: '/api/dental-examination/{dentalExamination}/file-download/{file}',
        tags: ['DentalExaminationFile'],
        summary: 'Pobiera zawartość pliku badania stomatologicznego',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'dentalExamination', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\PathParameter(name: 'file', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function download(DentalExamination $dentalExamination, File $file): JsonResponse
    {
        return new JsonResponse($this->fileService->getFile($file));
    }

    #[OA\Put(
        path: '/api/dental-examination/{dentalExamination}/file/{file}',
        tags: ['DentalExaminationFile'],
        summary: 'Zmienia nazwę pliku badania stomatologicznego',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'dentalExamination', schema: new OA\Schema(type: 'string', format: 'uuid')),
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
    public function update(DentalExamination $dentalExamination, File $file, FileUpdateRequest $request): FileResource
    {
        return new FileResource(
            $this->fileService->updateFileName($file, $request->input('filename'))
        );
    }

    #[OA\Delete(
        path: '/api/dental-examination/{dentalExamination}/file/{file}',
        tags: ['DentalExaminationFile'],
        summary: 'Usuwa plik badania stomatologicznego',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'dentalExamination', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\PathParameter(name: 'file', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Usunięto'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function destroy(DentalExamination $dentalExamination, File $file): JsonResponse
    {
        $this->fileService->deleteFile($file);

        return new JsonResponse(null, 204);
    }

    #[OA\Post(
        path: '/api/dental-examination/{dentalExamination}/file-new-version/{file}',
        tags: ['DentalExaminationFile'],
        summary: 'Tworzy nową wersję pliku badania stomatologicznego',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'dentalExamination', schema: new OA\Schema(type: 'string', format: 'uuid')),
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
    public function storeNewVersion(DentalExamination $dentalExamination, File $file, FileStoreRequest $request): AnonymousResourceCollection
    {
        return FileResource::collection(
            $this->fileService->createNewVersionFile(
                $file,
                new FileDto($request->file('files'), FileableType::DENTAL_EXAMINATION),
                $dentalExamination
            )
        );
    }
}
