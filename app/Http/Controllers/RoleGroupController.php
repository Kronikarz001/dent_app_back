<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignManagedUsersRequest;
use App\Http\Requests\AssignPermissionsRequest;
use App\Http\Requests\AssignRoleGroupRolesRequest;
use App\Http\Requests\DelegatePermissionRequest;
use App\Http\Requests\RoleGroupRequest;
use App\Http\Resources\RoleGroupResource;
use App\Models\RoleGroup;
use App\Services\RoleGroupServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;

/**
 * Summary of RoleGroupController
 */
class RoleGroupController extends Controller
{
    /**
     * @param RoleGroupServiceInterface $roleGroupService
     */
    public function __construct(
        private readonly RoleGroupServiceInterface $roleGroupService
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/role-group',
        summary: 'Lista grup ról (paginacja)',
        security: [['sanctum' => []]],
        tags: ['RoleGroup'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/RoleGroupResource')),
                        ]),
                    ]
                )
            ),
        ]
    )]
    public function index(): LengthAwarePaginator
    {
        return $this->roleGroupService->getRoleGroups();
    }

    /**
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/role-group/selectlist',
        summary: 'Lista grup ról do selecta (uuid + name)',
        security: [['sanctum' => []]],
        tags: ['RoleGroup'],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
        ]
    )]
    public function selectList(): LengthAwarePaginator
    {
        return $this->roleGroupService->getRoleGroupsList();
    }

    /**
     * @param RoleGroup $roleGroup
     * @return RoleGroupResource
     */
    #[OA\Get(
        path: '/api/role-group/{uuid}',
        summary: 'Pobiera jedną grupę ról',
        security: [['sanctum' => []]],
        tags: ['RoleGroup'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/RoleGroupResource')]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function show(RoleGroup $roleGroup): RoleGroupResource
    {
        return new RoleGroupResource($roleGroup->load('roles'));
    }

    /**
     * @param RoleGroupRequest $request
     * @return RoleGroupResource
     */
    #[OA\Post(
        path: '/api/role-group',
        summary: 'Tworzy nową grupę ról',
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
        tags: ['RoleGroup'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Utworzono',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/RoleGroupResource')]
                )
            ),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function store(RoleGroupRequest $request): RoleGroupResource
    {
        return new RoleGroupResource($this->roleGroupService->createRoleGroup($request->all()));
    }

    /**
     * @param RoleGroup $roleGroup
     * @param RoleGroupRequest $request
     * @return JsonResponse
     */
    #[OA\Put(
        path: '/api/role-group/{uuid}',
        summary: 'Aktualizuje grupę ról',
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
        tags: ['RoleGroup'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Zaktualizowano'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function update(RoleGroup $roleGroup, RoleGroupRequest $request): JsonResponse
    {
        $this->roleGroupService->updateRoleGroup($roleGroup, $request->all());

        return new JsonResponse(null, 204);
    }

    /**
     * @param RoleGroup $roleGroup
     * @return JsonResponse
     */
    #[OA\Delete(
        path: '/api/role-group/{uuid}',
        summary: 'Usuwa grupę ról',
        security: [['sanctum' => []]],
        tags: ['RoleGroup'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Usunięto'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function destroy(RoleGroup $roleGroup): JsonResponse
    {
        $this->roleGroupService->deleteRoleGroup($roleGroup);

        return new JsonResponse(null, 204);
    }

    /**
     * @param RoleGroup $roleGroup
     * @param AssignRoleGroupRolesRequest $request
     * @return JsonResponse
     */
    #[OA\Patch(
        path: '/api/role-group/{uuid}/roles',
        summary: 'Przypisuje role do grupy, zastępując poprzednią listę',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string', format: 'uuid')),
                ]
            )
        ),
        tags: ['RoleGroup'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Przypisano'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function assignRoles(RoleGroup $roleGroup, AssignRoleGroupRolesRequest $request): JsonResponse
    {
        $this->roleGroupService->assignRoles($roleGroup, $request->all());

        return new JsonResponse(null, 204);
    }

    /**
     * @param RoleGroup $roleGroup
     * @param AssignManagedUsersRequest $request
     * @return JsonResponse
     */
    #[OA\Patch(
        path: '/api/role-group/{uuid}/users',
        summary: 'Przypisuje użytkowników bezpośrednio do grupy ról (z flagą is_manager), zastępując poprzednią listę',
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
        tags: ['RoleGroup'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Przypisano'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function assignUsers(RoleGroup $roleGroup, AssignManagedUsersRequest $request): JsonResponse
    {
        $this->roleGroupService->assignUsers($roleGroup, $request->all());

        return new JsonResponse(null, 204);
    }

    /**
     * @param RoleGroup $roleGroup
     * @param AssignPermissionsRequest $request
     * @return JsonResponse
     */
    #[OA\Patch(
        path: '/api/role-group/{uuid}/permissions',
        summary: 'Nadaje grupie ról uprawnienia lub grupy uprawnień (opcjonalnie czasowo)',
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
        tags: ['RoleGroup'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Nadano'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function assignPermissions(RoleGroup $roleGroup, AssignPermissionsRequest $request): JsonResponse
    {
        $this->roleGroupService->assignPermissions($roleGroup, $request->all());

        return new JsonResponse(null, 204);
    }

    /**
     * @param RoleGroup $roleGroup
     * @param DelegatePermissionRequest $request
     * @return JsonResponse
     */
    #[OA\Post(
        path: '/api/role-group/{uuid}/delegate',
        summary: 'Kierownik grupy ról przekazuje własne uprawnienie (lub grupę uprawnień) innej osobie z tej grupy',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['user_uuid'],
                properties: [
                    new OA\Property(property: 'user_uuid', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'permission_uuid', type: 'string', format: 'uuid', nullable: true),
                    new OA\Property(property: 'permission_group_uuid', type: 'string', format: 'uuid', nullable: true),
                    new OA\Property(property: 'expires_at', type: 'string', format: 'date-time', nullable: true),
                ]
            )
        ),
        tags: ['RoleGroup'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Przekazano'),
            new OA\Response(response: 403, description: 'Brak uprawnień do przekazania lub brak statusu kierownika'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function delegate(RoleGroup $roleGroup, DelegatePermissionRequest $request): JsonResponse
    {
        $this->roleGroupService->delegate($roleGroup, $request->all());

        return new JsonResponse(null, 204);
    }
}
