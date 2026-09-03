<?php

namespace App\Http\Controllers;

use App\Dto\FileDto;
use App\Enums\FileableType;
use App\Http\Requests\FileStoreRequest;
use App\Http\Requests\FileUpdateRequest;
use App\Http\Requests\PhotoStoreRequest;
use App\Http\Resources\FileResource;
use App\Models\File;
use App\Models\User;
use App\Models\UserBackground;
use App\Services\FileServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;

class UserFileController extends Controller
{
    public function __construct(
        private readonly FileServiceInterface $fileService
    ) {}

    #[OA\Get(
        path: '/api/user/{user}/file',
        tags: ['UserFile'],
        summary: 'Lista plików użytkownika (paginacja)',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'user', schema: new OA\Schema(type: 'string', format: 'uuid')),
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
    public function index(User $user): LengthAwarePaginator
    {
        return $this->fileService->getAllFiles($user);
    }

    #[OA\Post(
        path: '/api/user/{user}/file',
        tags: ['UserFile'],
        summary: 'Wgrywa pliki dla użytkownika',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'user', schema: new OA\Schema(type: 'string', format: 'uuid')),
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
    public function store(User $user, FileStoreRequest $request): AnonymousResourceCollection
    {
        return FileResource::collection(
            $this->fileService->saveFile(
                new FileDto($request->file('files'), FileableType::USER),
                $user
            )
        );
    }

    #[OA\Get(
        path: '/api/user/{user}/file/{file}',
        tags: ['UserFile'],
        summary: 'Pobiera metadane pliku użytkownika',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'user', schema: new OA\Schema(type: 'string', format: 'uuid')),
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
    public function show(User $user, File $file): FileResource
    {
        $this->fileService->assertFileOwnedBy($file, $user);

        return new FileResource($file);
    }

    #[OA\Get(
        path: '/api/user/{user}/file-download/{file}',
        tags: ['UserFile'],
        summary: 'Pobiera zawartość pliku użytkownika',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'user', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\PathParameter(name: 'file', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function download(User $user, File $file): JsonResponse
    {
        $this->fileService->assertFileOwnedBy($file, $user);

        return new JsonResponse($this->fileService->getFile($file));
    }

    #[OA\Put(
        path: '/api/user/{user}/file/{file}',
        tags: ['UserFile'],
        summary: 'Zmienia nazwę pliku użytkownika',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'user', schema: new OA\Schema(type: 'string', format: 'uuid')),
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
    public function update(User $user, File $file, FileUpdateRequest $request): FileResource
    {
        $this->fileService->assertFileOwnedBy($file, $user);

        return new FileResource(
            $this->fileService->updateFileName($file, $request->input('filename'))
        );
    }

    #[OA\Delete(
        path: '/api/user/{user}/file/{file}',
        tags: ['UserFile'],
        summary: 'Usuwa plik użytkownika',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'user', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\PathParameter(name: 'file', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Usunięto'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function destroy(User $user, File $file): JsonResponse
    {
        $this->fileService->assertFileOwnedBy($file, $user);

        $this->fileService->deleteFile($file);

        return new JsonResponse(null, 204);
    }

    #[OA\Post(
        path: '/api/user/{user}/file-new-version/{file}',
        tags: ['UserFile'],
        summary: 'Tworzy nową wersję pliku użytkownika',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'user', schema: new OA\Schema(type: 'string', format: 'uuid')),
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
    public function storeNewVersion(User $user, File $file, FileStoreRequest $request): AnonymousResourceCollection
    {
        $this->fileService->assertFileOwnedBy($file, $user);

        return FileResource::collection(
            $this->fileService->createNewVersionFile(
                $file,
                new FileDto($request->file('files'), FileableType::USER),
                $user
            )
        );
    }

    #[OA\Post(
        path: '/api/user/{user}/avatar',
        summary: 'Wgrywa avatar użytkownika',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['file'],
                    properties: [
                        new OA\Property(property: 'file', type: 'string', format: 'binary'),
                    ]
                )
            )
        ),
        tags: ['UserFile'],
        parameters: [
            new OA\PathParameter(name: 'user', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
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
    public function storeAvatar(User $user, PhotoStoreRequest $request): AnonymousResourceCollection
    {
        return FileResource::collection(
            $this->fileService->saveAvatar($user, $request->file('file'))
        );
    }

    #[OA\Delete(
        path: '/api/user/{user}/avatar',
        tags: ['UserFile'],
        summary: 'Usuwa avatar użytkownika',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'user', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Usunięto'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function destroyAvatar(User $user): JsonResponse
    {
        $this->fileService->deleteAvatar($user);

        return new JsonResponse(null, 204);
    }

    #[OA\Post(
        path: '/api/user/{user}/background',
        summary: 'Wgrywa tło użytkownika',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['file'],
                    properties: [
                        new OA\Property(property: 'file', type: 'string', format: 'binary'),
                    ]
                )
            )
        ),
        tags: ['UserFile'],
        parameters: [
            new OA\PathParameter(name: 'user', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
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
    public function storeBackground(User $user, PhotoStoreRequest $request): AnonymousResourceCollection
    {
        return FileResource::collection(
            $this->fileService->saveFile(
                new FileDto([$request->file('file')], FileableType::USER_BACKGROUND),
                UserBackground::find($user->uuid)
            )
        );
    }

    #[OA\Get(
        path: '/api/user/{user}/file-background-download/{file}',
        summary: 'Pobiera tło użytkownika (obraz binarny)',
        security: [['sanctum' => []]],
        tags: ['UserFile'],
        parameters: [
            new OA\PathParameter(name: 'user', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\PathParameter(name: 'file', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Obraz', content: new OA\MediaType(mediaType: 'image/*')),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function backgroundDownload(User $user, File $file): Response
    {
        $this->fileService->assertFileOwnedBy($file, UserBackground::find($user->uuid));

        return response($this->fileService->getPhotoFile($file), 200, ['Content-Type' => $file->mimetype]);
    }
}
