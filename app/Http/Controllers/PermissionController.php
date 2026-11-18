<?php

namespace App\Http\Controllers;

use App\Http\Resources\PermissionResource;
use App\Models\Permission;
use App\Services\PermissionServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;

/**
 * Summary of PermissionController
 */
class PermissionController extends Controller
{
    /**
     * @param PermissionServiceInterface $permissionService
     */
    public function __construct(
        private readonly PermissionServiceInterface $permissionService
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/permission',
        summary: 'Lista uprawnień (paginacja)',
        security: [['sanctum' => []]],
        tags: ['Permission'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/PermissionResource')),
                        ]),
                    ]
                )
            ),
        ]
    )]
    public function index(): LengthAwarePaginator
    {
        return $this->permissionService->getPermissions();
    }

    /**
     * @return Collection
     */
    #[OA\Get(
        path: '/api/permission/selectlist',
        summary: 'Lista uprawnień do selecta (uuid + resource + type)',
        security: [['sanctum' => []]],
        tags: ['Permission'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(properties: [
                    new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'resource', type: 'string'),
                    new OA\Property(property: 'type', type: 'string'),
                ]))
            ),
        ]
    )]
    public function selectList(): Collection
    {
        return $this->permissionService->getPermissionsList();
    }

    /**
     * @param Permission $permission
     * @return PermissionResource
     */
    #[OA\Get(
        path: '/api/permission/{uuid}',
        summary: 'Pobiera jedno uprawnienie',
        security: [['sanctum' => []]],
        tags: ['Permission'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/PermissionResource')]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function show(Permission $permission): PermissionResource
    {
        return new PermissionResource($permission);
    }
}
