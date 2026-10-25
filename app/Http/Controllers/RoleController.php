<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignManagedUsersRequest;
use App\Http\Requests\AssignPermissionsRequest;
use App\Http\Requests\DelegatePermissionRequest;
use App\Http\Requests\RoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use App\Services\RoleServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

/**
 * Summary of RoleController
 */
class RoleController extends Controller
{
    /**
     * @param RoleServiceInterface $roleService
     */
    public function __construct(
        private readonly RoleServiceInterface $roleService
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/role',
        summary: 'Lista ról (paginacja)',
        security: [['sanctum' => []]],
        tags: ['Role'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/RoleResource')),
                        ]),
                    ]
                )
            ),
        ]
    )]
    public function index(): LengthAwarePaginator
    {
        return $this->roleService->getRoles();
    }

    /**
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/role/selectlist',
        summary: 'Lista ról do selecta (uuid + name)',
        security: [['sanctum' => []]],
        tags: ['Role'],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
        ]
    )]
    public function selectList(): LengthAwarePaginator
    {
        return $this->roleService->getRolesList();
    }

    /**
     * @param Role $role
     * @return RoleResource
     */
    #[OA\Get(
        path: '/api/role/{uuid}',
        summary: 'Pobiera jedną rolę',
        security: [['sanctum' => []]],
        tags: ['Role'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/RoleResource')]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function show(Role $role): RoleResource
    {
        return new RoleResource($role);
    }

    /**
     * @param RoleRequest $request
     * @return RoleResource
     */
    #[OA\Post(
        path: '/api/role',
        summary: 'Tworzy nową rolę',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(ref: '#/components/requestBodies/NameRequest'),
        tags: ['Role'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Utworzono',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/RoleResource')]
                )
            ),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function store(RoleRequest $request): RoleResource
    {
        return new RoleResource($this->roleService->createRole($request->all()));
    }

    /**
     * @param Role $role
     * @param RoleRequest $request
     * @return JsonResponse
     */
    #[OA\Put(
        path: '/api/role/{uuid}',
        summary: 'Aktualizuje rolę',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(ref: '#/components/requestBodies/NameRequest'),
        tags: ['Role'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Zaktualizowano'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function update(Role $role, RoleRequest $request): JsonResponse
    {
        $this->roleService->updateRole($role, $request->all());

        return new JsonResponse(null, 204);
    }

    /**
     * @param Role $role
     * @return JsonResponse
     */
    #[OA\Delete(
        path: '/api/role/{uuid}',
        summary: 'Usuwa rolę',
        security: [['sanctum' => []]],
        tags: ['Role'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Usunięto'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function destroy(Role $role): JsonResponse
    {
        $this->roleService->deleteRole($role);

        return new JsonResponse(null, 204);
    }

    /**
     * @param Role $role
     * @param AssignManagedUsersRequest $request
     * @return JsonResponse
     */
    #[OA\Patch(
        path: '/api/role/{uuid}/users',
        summary: 'Przypisuje użytkowników do roli (z flagą is_manager), zastępując poprzednią listę',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(ref: '#/components/requestBodies/AssignUsersRequest'),
        tags: ['Role'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Przypisano'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function assignUsers(Role $role, AssignManagedUsersRequest $request): JsonResponse
    {
        $this->roleService->assignUsers($role, $request->all());

        return new JsonResponse(null, 204);
    }

    /**
     * @param Role $role
     * @param AssignPermissionsRequest $request
     * @return JsonResponse
     */
    #[OA\Patch(
        path: '/api/role/{uuid}/permissions',
        summary: 'Nadaje roli uprawnienia lub grupy uprawnień (opcjonalnie czasowo)',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(ref: '#/components/requestBodies/AssignPermissionsRequest'),
        tags: ['Role'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Nadano'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function assignPermissions(Role $role, AssignPermissionsRequest $request): JsonResponse
    {
        $this->roleService->assignPermissions($role, $request->all());

        return new JsonResponse(null, 204);
    }

    /**
     * @param Role $role
     * @param DelegatePermissionRequest $request
     * @return JsonResponse
     */
    #[OA\Post(
        path: '/api/role/{uuid}/delegate',
        summary: 'Kierownik roli przekazuje własne uprawnienie (lub grupę uprawnień) innej osobie z tej roli',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(ref: '#/components/requestBodies/DelegatePermissionRequest'),
        tags: ['Role'],
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
    public function delegate(Role $role, DelegatePermissionRequest $request): JsonResponse
    {
        Gate::authorize('delegate', [$role, $request->input('user_uuid')]);
        $this->roleService->delegate($role, $request->all());

        return new JsonResponse(null, 204);
    }
}
