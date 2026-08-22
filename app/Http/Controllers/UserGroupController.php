<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignJobPositionsRequest;
use App\Http\Requests\AssignManagedUsersRequest;
use App\Http\Requests\AssignPermissionsRequest;
use App\Http\Requests\CreateManagedRoleRequest;
use App\Http\Requests\UserGroupRequest;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserGroupResource;
use App\Models\UserGroup;
use App\Services\UserGroupServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;

/**
 * Summary of UserGroupController
 */
class UserGroupController extends Controller
{
    /**
     * @param UserGroupServiceInterface $userGroupService
     */
    public function __construct(
        private readonly UserGroupServiceInterface $userGroupService
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/user-group',
        summary: 'Lista grup użytkowników (paginacja)',
        security: [['sanctum' => []]],
        tags: ['UserGroup'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/UserGroupResource')),
                        ]),
                    ]
                )
            ),
        ]
    )]
    public function index(): LengthAwarePaginator
    {
        return $this->userGroupService->getUserGroups();
    }

    /**
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/user-group/selectlist',
        summary: 'Lista grup użytkowników do selecta (uuid + name)',
        security: [['sanctum' => []]],
        tags: ['UserGroup'],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
        ]
    )]
    public function selectList(): LengthAwarePaginator
    {
        return $this->userGroupService->getUserGroupsList();
    }

    /**
     * @param UserGroup $userGroup
     * @return UserGroupResource
     */
    #[OA\Get(
        path: '/api/user-group/{uuid}',
        summary: 'Pobiera jedną grupę użytkowników',
        security: [['sanctum' => []]],
        tags: ['UserGroup'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/UserGroupResource')]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function show(UserGroup $userGroup): UserGroupResource
    {
        return new UserGroupResource($userGroup);
    }

    /**
     * @param UserGroupRequest $request
     * @return UserGroupResource
     */
    #[OA\Post(
        path: '/api/user-group',
        summary: 'Tworzy nową grupę użytkowników',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                ]
            )
        ),
        tags: ['UserGroup'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Utworzono',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/UserGroupResource')]
                )
            ),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function store(UserGroupRequest $request): UserGroupResource
    {
        return new UserGroupResource($this->userGroupService->createUserGroup($request->all()));
    }

    /**
     * @param UserGroup $userGroup
     * @param UserGroupRequest $request
     * @return JsonResponse
     */
    #[OA\Put(
        path: '/api/user-group/{uuid}',
        summary: 'Aktualizuje grupę użytkowników',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                ]
            )
        ),
        tags: ['UserGroup'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Zaktualizowano'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function update(UserGroup $userGroup, UserGroupRequest $request): JsonResponse
    {
        $this->userGroupService->updateUserGroup($userGroup, $request->all());

        return new JsonResponse(null, 204);
    }

    /**
     * @param UserGroup $userGroup
     * @return JsonResponse
     */
    #[OA\Delete(
        path: '/api/user-group/{uuid}',
        summary: 'Usuwa grupę użytkowników',
        security: [['sanctum' => []]],
        tags: ['UserGroup'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Usunięto'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function destroy(UserGroup $userGroup): JsonResponse
    {
        $this->userGroupService->deleteUserGroup($userGroup);

        return new JsonResponse(null, 204);
    }

    /**
     * @param UserGroup $userGroup
     * @param AssignManagedUsersRequest $request
     * @return JsonResponse
     */
    #[OA\Patch(
        path: '/api/user-group/{uuid}/users',
        summary: 'Przypisuje użytkowników do grupy (z flagą is_manager), zastępując poprzednią listę',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'users', type: 'array', items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
                            new OA\Property(property: 'is_manager', type: 'boolean'),
                        ]
                    )),
                ]
            )
        ),
        tags: ['UserGroup'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Przypisano'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function assignUsers(UserGroup $userGroup, AssignManagedUsersRequest $request): JsonResponse
    {
        $this->userGroupService->assignUsers($userGroup, $request->all());

        return new JsonResponse(null, 204);
    }

    /**
     * @param UserGroup $userGroup
     * @param AssignPermissionsRequest $request
     * @return JsonResponse
     */
    #[OA\Patch(
        path: '/api/user-group/{uuid}/permissions',
        summary: 'Nadaje grupie użytkowników uprawnienia lub grupy uprawnień (opcjonalnie czasowo)',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'permissions', type: 'array', items: new OA\Items(type: 'string', format: 'uuid')),
                    new OA\Property(property: 'permission_groups', type: 'array', items: new OA\Items(type: 'string', format: 'uuid')),
                    new OA\Property(property: 'expires_at', type: 'string', format: 'date-time', nullable: true),
                ]
            )
        ),
        tags: ['UserGroup'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Nadano'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function assignPermissions(UserGroup $userGroup, AssignPermissionsRequest $request): JsonResponse
    {
        $this->userGroupService->assignPermissions($userGroup, $request->all());

        return new JsonResponse(null, 204);
    }

    /**
     * @param UserGroup $userGroup
     * @param CreateManagedRoleRequest $request
     * @return RoleResource
     */
    #[OA\Post(
        path: '/api/user-group/{uuid}/managed-roles',
        summary: 'Kierownik grupy użytkowników tworzy nową rolę w ramach grupy i nadaje jej uprawnienia (muszą być podzbiorem uprawnień grupy)',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'permissions', type: 'array', items: new OA\Items(type: 'string', format: 'uuid')),
                    new OA\Property(property: 'permission_groups', type: 'array', items: new OA\Items(type: 'string', format: 'uuid')),
                ]
            )
        ),
        tags: ['UserGroup'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Utworzono',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/RoleResource')]
                )
            ),
            new OA\Response(response: 403, description: 'Brak statusu kierownika lub uprawnienie/grupa wykracza poza uprawnienia grupy'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function createRole(UserGroup $userGroup, CreateManagedRoleRequest $request): RoleResource
    {
        return new RoleResource($this->userGroupService->createManagedRole($userGroup, $request->all()));
    }

    /**
     * @param UserGroup $userGroup
     * @param AssignJobPositionsRequest $request
     * @return JsonResponse
     */
    #[OA\Patch(
        path: '/api/user-group/{uuid}/job-positions',
        summary: 'Przypisuje stanowiska do grupy użytkowników, zastępując poprzednią listę',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'job_positions', type: 'array', items: new OA\Items(type: 'string', format: 'uuid')),
                ]
            )
        ),
        tags: ['UserGroup'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Przypisano'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function assignJobPositions(UserGroup $userGroup, AssignJobPositionsRequest $request): JsonResponse
    {
        $this->userGroupService->assignJobPositions($userGroup, $request->all());

        return new JsonResponse(null, 204);
    }
}
