<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExportRequest;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserController extends Controller
{
    public function __construct(
        private readonly UserServiceInterface $userService
    ) {}

    #[OA\Get(
        path: '/api/user',
        tags: ['User'],
        summary: 'Lista użytkowników (paginacja)',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/UserResource')),
                        ]),
                    ]
                )
            ),
        ]
    )]
    public function index(): LengthAwarePaginator
    {
        return $this->userService->getUsers();
    }

    #[OA\Get(
        path: '/api/user/selectlist',
        tags: ['User'],
        summary: 'Lista użytkowników do selecta (uuid + name)',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
        ]
    )]
    public function selectList(): LengthAwarePaginator
    {
        return $this->userService->getUsersList();
    }

    #[OA\Get(
        path: '/api/user/{uuid}',
        tags: ['User'],
        summary: 'Pobiera jednego użytkownika',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/UserResource')]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function show(User $user): UserResource
    {
        return new UserResource($user);
    }

    #[OA\Get(
        path: '/api/user/user-info',
        tags: ['User'],
        summary: 'Dane zalogowanego użytkownika',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/UserResource')]
                )
            ),
        ]
    )]
    public function showLoggedUser(): UserResource
    {
        return new UserResource($this->userService->getLoggedUser());
    }

    #[OA\Post(
        path: '/api/user',
        tags: ['User'],
        summary: 'Tworzy nowego użytkownika',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['first_name', 'last_name', 'email', 'pesel', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'first_name', type: 'string', example: 'Jan'),
                    new OA\Property(property: 'last_name', type: 'string', example: 'Kowalski'),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'pesel', type: 'string', example: '12345678901'),
                    new OA\Property(property: 'password', type: 'string', format: 'password'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Utworzono',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/UserResource')]
                )
            ),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function store(UserStoreRequest $request): UserResource
    {
        return new UserResource($this->userService->createUser($request->all()));
    }

    #[OA\Put(
        path: '/api/user/{uuid}',
        tags: ['User'],
        summary: 'Aktualizuje użytkownika',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['first_name', 'last_name', 'email', 'pesel'],
                properties: [
                    new OA\Property(property: 'first_name', type: 'string'),
                    new OA\Property(property: 'last_name', type: 'string'),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'pesel', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 204, description: 'Zaktualizowano'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function update(User $user, UserUpdateRequest $request): JsonResponse
    {
        $this->userService->updateUser($user, $request->all());

        return new JsonResponse(null, 204);
    }

    #[OA\Delete(
        path: '/api/user/{uuid}',
        tags: ['User'],
        summary: 'Deaktywuje użytkownika',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Deaktywowano'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function destroy(User $user): JsonResponse
    {
        $this->userService->deactivateUser($user);

        return new JsonResponse(null, 204);
    }

    #[OA\Get(
        path: '/api/user/export',
        tags: ['User'],
        summary: 'Eksport użytkowników do pliku',
        security: [['sanctum' => []]],
        parameters: [
            new OA\QueryParameter(name: 'type', required: true, schema: new OA\Schema(type: 'string', enum: ['xlsx', 'csv', 'ods'])),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Plik do pobrania', content: new OA\MediaType(mediaType: 'application/octet-stream')),
        ]
    )]
    public function export(ExportRequest $request): BinaryFileResponse
    {
        return $this->userService->export($request);
    }
}
