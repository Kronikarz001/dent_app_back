<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignEmployeeScheduleUsersRequest;
use App\Http\Requests\EmployeeScheduleIndexRequest;
use App\Http\Requests\EmployeeScheduleRequest;
use App\Http\Resources\EmployeeScheduleResource;
use App\Models\EmployeeSchedule;
use App\Services\EmployeeScheduleServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;

/**
 * Summary of EmployeeScheduleController
 */
class EmployeeScheduleController extends Controller
{
    /**
     * @param EmployeeScheduleServiceInterface $employeeScheduleService
     */
    public function __construct(
        private readonly EmployeeScheduleServiceInterface $employeeScheduleService
    ) {}

    /**
     * @param EmployeeScheduleIndexRequest $request
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/employee-schedule',
        summary: 'Lista wydarzeń w kalendarzu pracowników (z przypisanymi pracownikami)',
        security: [['sanctum' => []]],
        tags: ['EmployeeSchedule'],
        parameters: [
            new OA\QueryParameter(name: 'employeeSchedule[user_uuids][]', description: 'Filtr po pracownikach', required: false, schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string', format: 'uuid'))),
            new OA\QueryParameter(name: 'employeeSchedule[date_from]', description: 'Wpisy kończące się nie wcześniej niż ta data', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\QueryParameter(name: 'employeeSchedule[date_to]', description: 'Wpisy zaczynające się nie później niż ta data', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/EmployeeScheduleResource')),
                        ]),
                    ]
                )
            ),
        ]
    )]
    public function index(EmployeeScheduleIndexRequest $request): LengthAwarePaginator
    {
        return $this->employeeScheduleService->getSchedules($request->filters());
    }

    /**
     * @param EmployeeScheduleIndexRequest $request
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/employee-schedule/my',
        summary: 'Grafik zalogowanego pracownika',
        security: [['sanctum' => []]],
        tags: ['EmployeeSchedule'],
        parameters: [
            new OA\QueryParameter(name: 'employeeSchedule[date_from]', description: 'Wpisy kończące się nie wcześniej niż ta data', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\QueryParameter(name: 'employeeSchedule[date_to]', description: 'Wpisy zaczynające się nie później niż ta data', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/EmployeeScheduleResource')),
                        ]),
                    ]
                )
            ),
        ]
    )]
    public function my(EmployeeScheduleIndexRequest $request): LengthAwarePaginator
    {
        return $this->employeeScheduleService->getMySchedules($request->filters());
    }

    /**
     * @return Collection
     */
    #[OA\Get(
        path: '/api/employee-schedule/selectlist',
        summary: 'Wydarzenia do selecta (uuid + name)',
        security: [['sanctum' => []]],
        tags: ['EmployeeSchedule'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/SelectListItem'))
            ),
        ]
    )]
    public function selectList(): Collection
    {
        return $this->employeeScheduleService->getSchedulesList();
    }

    /**
     * @param EmployeeSchedule $employeeSchedule
     * @return EmployeeScheduleResource
     */
    #[OA\Get(
        path: '/api/employee-schedule/{uuid}',
        summary: 'Pobiera jedno wydarzenie',
        security: [['sanctum' => []]],
        tags: ['EmployeeSchedule'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/EmployeeScheduleResource')]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function show(EmployeeSchedule $employeeSchedule): EmployeeScheduleResource
    {
        return new EmployeeScheduleResource($this->employeeScheduleService->getScheduleDetails($employeeSchedule));
    }

    /**
     * @param EmployeeScheduleRequest $request
     * @return EmployeeScheduleResource
     */
    #[OA\Post(
        path: '/api/employee-schedule',
        summary: 'Tworzy nowe wydarzenie w kalendarzu pracowników',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(ref: '#/components/requestBodies/EmployeeScheduleRequest'),
        tags: ['EmployeeSchedule'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Utworzono',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/EmployeeScheduleResource')]
                )
            ),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function store(EmployeeScheduleRequest $request): EmployeeScheduleResource
    {
        return new EmployeeScheduleResource($this->employeeScheduleService->createSchedule($request->all()));
    }

    /**
     * @param EmployeeSchedule $employeeSchedule
     * @param EmployeeScheduleRequest $request
     * @return JsonResponse
     */
    #[OA\Put(
        path: '/api/employee-schedule/{uuid}',
        summary: 'Aktualizuje wydarzenie w kalendarzu pracowników',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(ref: '#/components/requestBodies/EmployeeScheduleRequest'),
        tags: ['EmployeeSchedule'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Zaktualizowano'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function update(EmployeeSchedule $employeeSchedule, EmployeeScheduleRequest $request): JsonResponse
    {
        $this->employeeScheduleService->updateSchedule($employeeSchedule, $request->all());

        return new JsonResponse(null, 204);
    }

    /**
     * @param EmployeeSchedule $employeeSchedule
     * @return JsonResponse
     */
    #[OA\Delete(
        path: '/api/employee-schedule/{uuid}',
        summary: 'Usuwa wydarzenie z kalendarza pracowników',
        security: [['sanctum' => []]],
        tags: ['EmployeeSchedule'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Usunięto'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function destroy(EmployeeSchedule $employeeSchedule): JsonResponse
    {
        $this->employeeScheduleService->deleteSchedule($employeeSchedule);

        return new JsonResponse(null, 204);
    }

    /**
     * @param EmployeeSchedule $employeeSchedule
     * @param AssignEmployeeScheduleUsersRequest $request
     * @return JsonResponse
     */
    #[OA\Patch(
        path: '/api/employee-schedule/{uuid}/users',
        summary: 'Przypisuje pracowników do wydarzenia, zastępując poprzednią listę',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'users', type: 'array', items: new OA\Items(type: 'string', format: 'uuid')),
                ]
            )
        ),
        tags: ['EmployeeSchedule'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Przypisano'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function assignUsers(EmployeeSchedule $employeeSchedule, AssignEmployeeScheduleUsersRequest $request): JsonResponse
    {
        $this->employeeScheduleService->assignUsers($employeeSchedule, $request->all());

        return new JsonResponse(null, 204);
    }
}
