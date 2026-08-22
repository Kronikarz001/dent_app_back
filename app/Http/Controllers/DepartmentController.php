<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignDepartmentRolesRequest;
use App\Http\Requests\AssignJobPositionsRequest;
use App\Http\Requests\AssignManagedUsersRequest;
use App\Http\Requests\AssignPermissionsRequest;
use App\Http\Requests\CreateManagedRoleRequest;
use App\Http\Requests\DelegatePermissionRequest;
use App\Http\Requests\DepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Http\Resources\RoleResource;
use App\Models\Department;
use App\Services\DepartmentServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;

/**
 * Summary of DepartmentController
 */
class DepartmentController extends Controller
{
    /**
     * @param DepartmentServiceInterface $departmentService
     */
    public function __construct(
        private readonly DepartmentServiceInterface $departmentService
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/department',
        summary: 'Lista działów (paginacja)',
        security: [['sanctum' => []]],
        tags: ['Department'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/DepartmentResource')),
                        ]),
                    ]
                )
            ),
        ]
    )]
    public function index(): LengthAwarePaginator
    {
        return $this->departmentService->getDepartments();
    }

    /**
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/department/selectlist',
        summary: 'Lista działów do selecta (uuid + name)',
        security: [['sanctum' => []]],
        tags: ['Department'],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
        ]
    )]
    public function selectList(): LengthAwarePaginator
    {
        return $this->departmentService->getDepartmentsList();
    }

    /**
     * @param Department $department
     * @return DepartmentResource
     */
    #[OA\Get(
        path: '/api/department/{uuid}',
        summary: 'Pobiera jeden dział',
        security: [['sanctum' => []]],
        tags: ['Department'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/DepartmentResource')]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function show(Department $department): DepartmentResource
    {
        return new DepartmentResource($department->load('roles'));
    }

    /**
     * @param DepartmentRequest $request
     * @return DepartmentResource
     */
    #[OA\Post(
        path: '/api/department',
        summary: 'Tworzy nowy dział',
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
        tags: ['Department'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Utworzono',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/DepartmentResource')]
                )
            ),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function store(DepartmentRequest $request): DepartmentResource
    {
        return new DepartmentResource($this->departmentService->createDepartment($request->all()));
    }

    /**
     * @param Department $department
     * @param DepartmentRequest $request
     * @return JsonResponse
     */
    #[OA\Put(
        path: '/api/department/{uuid}',
        summary: 'Aktualizuje dział',
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
        tags: ['Department'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Zaktualizowano'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function update(Department $department, DepartmentRequest $request): JsonResponse
    {
        $this->departmentService->updateDepartment($department, $request->all());

        return new JsonResponse(null, 204);
    }

    /**
     * @param Department $department
     * @return JsonResponse
     */
    #[OA\Delete(
        path: '/api/department/{uuid}',
        summary: 'Usuwa dział',
        security: [['sanctum' => []]],
        tags: ['Department'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Usunięto'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function destroy(Department $department): JsonResponse
    {
        $this->departmentService->deleteDepartment($department);

        return new JsonResponse(null, 204);
    }

    /**
     * @param Department $department
     * @param AssignDepartmentRolesRequest $request
     * @return JsonResponse
     */
    #[OA\Patch(
        path: '/api/department/{uuid}/roles',
        summary: 'Przypisuje role do działu, zastępując poprzednią listę',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string', format: 'uuid')),
                ]
            )
        ),
        tags: ['Department'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Przypisano'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function assignRoles(Department $department, AssignDepartmentRolesRequest $request): JsonResponse
    {
        $this->departmentService->assignRoles($department, $request->all());

        return new JsonResponse(null, 204);
    }

    /**
     * @param Department $department
     * @param AssignJobPositionsRequest $request
     * @return JsonResponse
     */
    #[OA\Patch(
        path: '/api/department/{uuid}/job-positions',
        summary: 'Przypisuje stanowiska do działu, zastępując poprzednią listę',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'job_positions', type: 'array', items: new OA\Items(type: 'string', format: 'uuid')),
                ]
            )
        ),
        tags: ['Department'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Przypisano'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function assignJobPositions(Department $department, AssignJobPositionsRequest $request): JsonResponse
    {
        $this->departmentService->assignJobPositions($department, $request->all());

        return new JsonResponse(null, 204);
    }

    /**
     * @param Department $department
     * @param AssignManagedUsersRequest $request
     * @return JsonResponse
     */
    #[OA\Patch(
        path: '/api/department/{uuid}/users',
        summary: 'Przypisuje użytkowników bezpośrednio do działu (z flagą is_manager), zastępując poprzednią listę',
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
        tags: ['Department'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Przypisano'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function assignUsers(Department $department, AssignManagedUsersRequest $request): JsonResponse
    {
        $this->departmentService->assignUsers($department, $request->all());

        return new JsonResponse(null, 204);
    }

    /**
     * @param Department $department
     * @param AssignPermissionsRequest $request
     * @return JsonResponse
     */
    #[OA\Patch(
        path: '/api/department/{uuid}/permissions',
        summary: 'Nadaje działowi uprawnienia lub grupy uprawnień (opcjonalnie czasowo)',
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
        tags: ['Department'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Nadano'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function assignPermissions(Department $department, AssignPermissionsRequest $request): JsonResponse
    {
        $this->departmentService->assignPermissions($department, $request->all());

        return new JsonResponse(null, 204);
    }

    /**
     * @param Department $department
     * @param DelegatePermissionRequest $request
     * @return JsonResponse
     */
    #[OA\Post(
        path: '/api/department/{uuid}/delegate',
        summary: 'Kierownik działu przekazuje własne uprawnienie (lub grupę uprawnień) innej osobie z tego działu',
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
        tags: ['Department'],
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
    public function delegate(Department $department, DelegatePermissionRequest $request): JsonResponse
    {
        $this->departmentService->delegate($department, $request->all());

        return new JsonResponse(null, 204);
    }

    /**
     * @param Department $department
     * @param CreateManagedRoleRequest $request
     * @return RoleResource
     */
    #[OA\Post(
        path: '/api/department/{uuid}/managed-roles',
        summary: 'Kierownik działu tworzy nową rolę w ramach działu i nadaje jej uprawnienia (muszą być podzbiorem uprawnień działu)',
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
        tags: ['Department'],
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
            new OA\Response(response: 403, description: 'Brak statusu kierownika lub uprawnienie/grupa wykracza poza uprawnienia działu'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function createRole(Department $department, CreateManagedRoleRequest $request): RoleResource
    {
        return new RoleResource($this->departmentService->createManagedRole($department, $request->all()));
    }
}
