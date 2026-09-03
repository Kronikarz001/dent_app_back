<?php

namespace App\Http\Controllers;

use App\Dto\FileDto;
use App\Enums\FileableType;
use App\Http\Requests\FileStoreRequest;
use App\Http\Requests\FileUpdateRequest;
use App\Http\Resources\FileResource;
use App\Models\File;
use App\Models\Message;
use App\Services\FileServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;

/**
 * Summary of MessageFileController
 */
class MessageFileController extends Controller
{
    /**
     * @param FileServiceInterface $fileService
     */
    public function __construct(
        private readonly FileServiceInterface $fileService
    ) {}

    /**
     * @param Message $message
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/message/{message}/file',
        summary: 'Lista plików załączonych do wiadomości',
        security: [['sanctum' => []]],
        tags: ['MessageFile'],
        parameters: [
            new OA\PathParameter(name: 'message', schema: new OA\Schema(type: 'string', format: 'uuid')),
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
    public function index(Message $message): LengthAwarePaginator
    {
        return $this->fileService->getAllFiles($message);
    }

    /**
     * @param Message $message
     * @param FileStoreRequest $request
     * @return AnonymousResourceCollection
     */
    #[OA\Post(
        path: '/api/message/{message}/file',
        summary: 'Dodaje pliki do wiadomości',
        security: [['sanctum' => []]],
        tags: ['MessageFile'],
        parameters: [
            new OA\PathParameter(name: 'message', schema: new OA\Schema(type: 'string', format: 'uuid')),
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
    public function store(Message $message, FileStoreRequest $request): AnonymousResourceCollection
    {
        return FileResource::collection(
            $this->fileService->saveFile(
                new FileDto($request->file('files'), FileableType::MESSAGE),
                $message
            )
        );
    }

    /**
     * @param Message $message
     * @param File $file
     * @return FileResource
     */
    #[OA\Get(
        path: '/api/message/{message}/file/{file}',
        summary: 'Pobiera metadane pliku wiadomości',
        security: [['sanctum' => []]],
        tags: ['MessageFile'],
        parameters: [
            new OA\PathParameter(name: 'message', schema: new OA\Schema(type: 'string', format: 'uuid')),
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
    public function show(Message $message, File $file): FileResource
    {
        $this->assertFileBelongsTo($file, $message);

        return new FileResource($file);
    }

    /**
     * @param Message $message
     * @param File $file
     * @return JsonResponse
     */
    #[OA\Get(
        path: '/api/message/{message}/file-download/{file}',
        summary: 'Pobiera zawartość pliku wiadomości',
        security: [['sanctum' => []]],
        tags: ['MessageFile'],
        parameters: [
            new OA\PathParameter(name: 'message', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\PathParameter(name: 'file', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function download(Message $message, File $file): JsonResponse
    {
        $this->assertFileBelongsTo($file, $message);

        return new JsonResponse($this->fileService->getFile($file));
    }

    /**
     * @param Message $message
     * @param File $file
     * @param FileUpdateRequest $request
     * @return FileResource
     */
    #[OA\Put(
        path: '/api/message/{message}/file/{file}',
        summary: 'Zmienia nazwę pliku wiadomości',
        security: [['sanctum' => []]],
        tags: ['MessageFile'],
        parameters: [
            new OA\PathParameter(name: 'message', schema: new OA\Schema(type: 'string', format: 'uuid')),
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
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function update(Message $message, File $file, FileUpdateRequest $request): FileResource
    {
        $this->assertFileBelongsTo($file, $message);

        return new FileResource(
            $this->fileService->updateFileName($file, $request->input('filename'))
        );
    }

    /**
     * @param Message $message
     * @param File $file
     * @return JsonResponse
     */
    #[OA\Delete(
        path: '/api/message/{message}/file/{file}',
        summary: 'Usuwa plik wiadomości',
        security: [['sanctum' => []]],
        tags: ['MessageFile'],
        parameters: [
            new OA\PathParameter(name: 'message', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\PathParameter(name: 'file', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Usunięto'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function destroy(Message $message, File $file): JsonResponse
    {
        $this->assertFileBelongsTo($file, $message);

        $this->fileService->deleteFile($file);

        return new JsonResponse(null, 204);
    }
}
