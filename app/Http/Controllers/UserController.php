<?php

namespace App\Http\Controllers;

use App\Http\Requests\EditPasswordRequest;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\LoggedUserResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Summary of UserController
 */
class UserController extends Controller
{
    /**
     * @param UserServiceInterface $userService
     */
    public function __construct(
        private readonly UserServiceInterface $userService
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/user',
        summary: 'Lista użytkowników (paginacja)',
        security: [['sanctum' => []]],
        tags: ['User'],
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

    /**
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/user/selectlist',
        summary: 'Lista użytkowników do selecta (uuid + name)',
        security: [['sanctum' => []]],
        tags: ['User'],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
        ]
    )]
    public function selectList(): LengthAwarePaginator
    {
        return $this->userService->getUsersList();
    }

    /**
     * @param User $user
     * @return UserResource
     */
    #[OA\Get(
        path: '/api/user/{uuid}',
        summary: 'Pobiera jednego użytkownika',
        security: [['sanctum' => []]],
        tags: ['User'],
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

    /**
     * @return LoggedUserResource
     */
    #[OA\Get(
        path: '/api/user/user-info',
        summary: 'Dane zalogowanego użytkownika',
        security: [['sanctum' => []]],
        tags: ['User'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/LoggedUserResource')]
                )
            ),
        ]
    )]
    public function showLoggedUser(): LoggedUserResource
    {
        return new LoggedUserResource($this->userService->getLoggedUser());
    }

    /**
     * @param UserStoreRequest $request
     * @return UserResource
     */
    #[OA\Post(
        path: '/api/user',
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
        tags: ['User'],
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

    /**
     * @param User $user
     * @param UserUpdateRequest $request
     * @return JsonResponse
     */
    #[OA\Put(
        path: '/api/user/{uuid}',
        summary: 'Aktualizuje użytkownika',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['first_name', 'last_name', 'email', 'pesel'],
                properties: [
                    new OA\Property(property: 'first_name', type: 'string'),
                    new OA\Property(property: 'last_name', type: 'string'),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'pesel', type: 'string'),
                    new OA\Property(property: 'job_positions', type: 'array', items: new OA\Items(type: 'string', format: 'uuid')),
                ]
            )
        ),
        tags: ['User'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
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

    /**
     * @param User $user
     * @return JsonResponse
     */
    #[OA\Delete(
        path: '/api/user/{uuid}',
        summary: 'Deaktywuje użytkownika',
        security: [['sanctum' => []]],
        tags: ['User'],
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

    /**
     * @param ExportRequest $request
     * @return BinaryFileResponse
     *
     * @throws Exception
     * @throws WriterException
     */
    #[OA\Get(
        path: '/api/user/export',
        summary: 'Eksport użytkowników do pliku',
        security: [['sanctum' => []]],
        tags: ['User'],
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

    /**
     * @param EditPasswordRequest $request
     * @return JsonResponse
     */
    #[OA\Patch(
        path: '/api/user/edit-password',
        summary: 'Zmiana hasła przez zalogowanego użytkownika',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['current_password', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'current_password', type: 'string', format: 'password'),
                    new OA\Property(property: 'password', type: 'string', format: 'password'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
                ]
            )
        ),
        tags: ['User'],
        responses: [
            new OA\Response(response: 204, description: 'Hasło zmienione'),
            new OA\Response(response: 422, description: 'Nieprawidłowe obecne hasło lub błąd walidacji'),
        ]
    )]
    public function editPassword(EditPasswordRequest $request): JsonResponse
    {
        $this->userService->editPassword($this->userService->getLoggedUser(), $request->all());

        return new JsonResponse(null, 204);
    }
}
