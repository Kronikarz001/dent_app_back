<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignPermissionGroupPermissionsRequest;
use App\Http\Requests\PermissionGroupRequest;
use App\Http\Resources\PermissionGroupResource;
use App\Models\PermissionGroup;
use App\Services\PermissionGroupServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;

/**
 * Summary of PermissionGroupController
 */
class PermissionGroupController extends Controller
{
    /**
     * @param PermissionGroupServiceInterface $permissionGroupService
     */
    public function __construct(
        private readonly PermissionGroupServiceInterface $permissionGroupService
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/permission-group',
        summary: 'Lista grup uprawnień (paginacja)',
        security: [['sanctum' => []]],
        tags: ['PermissionGroup'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/PermissionGroupResource')),
                        ]),
                    ]
                )
            ),
        ]
    )]
    public function index(): LengthAwarePaginator
    {
        return $this->permissionGroupService->getPermissionGroups();
    }

    /**
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/permission-group/selectlist',
        summary: 'Lista grup uprawnień do selecta (uuid + name)',
        security: [['sanctum' => []]],
        tags: ['PermissionGroup'],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
        ]
    )]
    public function selectList(): LengthAwarePaginator
    {
        return $this->permissionGroupService->getPermissionGroupsList();
    }

    /**
     * @param PermissionGroup $permissionGroup
     * @return PermissionGroupResource
     */
    #[OA\Get(
        path: '/api/permission-group/{uuid}',
        summary: 'Pobiera jedną grupę uprawnień',
        security: [['sanctum' => []]],
        tags: ['PermissionGroup'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/PermissionGroupResource')]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function show(PermissionGroup $permissionGroup): PermissionGroupResource
    {
        return new PermissionGroupResource($permissionGroup->load('permissions'));
    }

    /**
     * @param PermissionGroupRequest $request
     * @return PermissionGroupResource
     */
    #[OA\Post(
        path: '/api/permission-group',
        summary: 'Tworzy nową grupę uprawnień',
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
        tags: ['PermissionGroup'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Utworzono',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/PermissionGroupResource')]
                )
            ),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function store(PermissionGroupRequest $request): PermissionGroupResource
    {
        return new PermissionGroupResource($this->permissionGroupService->createPermissionGroup($request->all()));
    }

    /**
     * @param PermissionGroup $permissionGroup
     * @param PermissionGroupRequest $request
     * @return JsonResponse
     */
    #[OA\Put(
        path: '/api/permission-group/{uuid}',
        summary: 'Aktualizuje grupę uprawnień',
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
        tags: ['PermissionGroup'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Zaktualizowano'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function update(PermissionGroup $permissionGroup, PermissionGroupRequest $request): JsonResponse
    {
        $this->permissionGroupService->updatePermissionGroup($permissionGroup, $request->all());

        return new JsonResponse(null, 204);
    }

    /**
     * @param PermissionGroup $permissionGroup
     * @return JsonResponse
     */
    #[OA\Delete(
        path: '/api/permission-group/{uuid}',
        summary: 'Usuwa grupę uprawnień',
        security: [['sanctum' => []]],
        tags: ['PermissionGroup'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Usunięto'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function destroy(PermissionGroup $permissionGroup): JsonResponse
    {
        $this->permissionGroupService->deletePermissionGroup($permissionGroup);

        return new JsonResponse(null, 204);
    }

    /**
     * @param PermissionGroup $permissionGroup
     * @param AssignPermissionGroupPermissionsRequest $request
     * @return JsonResponse
     */
    #[OA\Patch(
        path: '/api/permission-group/{uuid}/permissions',
        summary: 'Przypisuje uprawnienia do grupy, zastępując poprzednią listę',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'permissions', type: 'array', items: new OA\Items(type: 'string', format: 'uuid')),
                ]
            )
        ),
        tags: ['PermissionGroup'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Przypisano'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function assignPermissions(PermissionGroup $permissionGroup, AssignPermissionGroupPermissionsRequest $request): JsonResponse
    {
        $this->permissionGroupService->assignPermissions($permissionGroup, $request->all());

        return new JsonResponse(null, 204);
    }
}
