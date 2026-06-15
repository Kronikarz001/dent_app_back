<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignJobPositionRequest;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\JobPositionRequest;
use App\Http\Resources\JobPositionResource;
use App\Models\JobPosition;
use App\Models\User;
use App\Services\JobPositionServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Summary of JobPositionController
 */
class JobPositionController extends Controller
{
    /**
     * @param JobPositionServiceInterface $jobPositionService
     */
    public function __construct(
        private readonly JobPositionServiceInterface $jobPositionService
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/job-position',
        summary: 'Lista stanowisk (paginacja)',
        security: [['sanctum' => []]],
        tags: ['JobPosition'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/JobPositionResource')),
                        ]),
                    ]
                )
            ),
        ]
    )]
    public function index(): LengthAwarePaginator
    {
        return $this->jobPositionService->getJobPositions();
    }

    /**
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/job-position/selectlist',
        summary: 'Lista stanowisk do selecta (uuid + name)',
        security: [['sanctum' => []]],
        tags: ['JobPosition'],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
        ]
    )]
    public function selectList(): LengthAwarePaginator
    {
        return $this->jobPositionService->getJobPositionsList();
    }

    /**
     * @param JobPosition $jobPosition
     * @return JobPositionResource
     */
    #[OA\Get(
        path: '/api/job-position/{uuid}',
        summary: 'Pobiera jedno stanowisko',
        security: [['sanctum' => []]],
        tags: ['JobPosition'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/JobPositionResource')]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function show(JobPosition $jobPosition): JobPositionResource
    {
        return new JobPositionResource($jobPosition);
    }

    /**
     * @param JobPositionRequest $request
     * @return JobPositionResource
     */
    #[OA\Post(
        path: '/api/job-position',
        summary: 'Tworzy nowe stanowisko',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'f_name', 'm_name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Stomatolog'),
                    new OA\Property(property: 'f_name', type: 'string', example: 'Stomatolożka'),
                    new OA\Property(property: 'm_name', type: 'string', example: 'Stomatolodzy'),
                ]
            )
        ),
        tags: ['JobPosition'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Utworzono',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/JobPositionResource')]
                )
            ),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function store(JobPositionRequest $request): JobPositionResource
    {
        return new JobPositionResource($this->jobPositionService->createJobPosition($request->all()));
    }

    /**
     * @param JobPosition $jobPosition
     * @param JobPositionRequest $request
     * @return JsonResponse
     */
    #[OA\Put(
        path: '/api/job-position/{uuid}',
        summary: 'Aktualizuje stanowisko',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'f_name', 'm_name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'f_name', type: 'string'),
                    new OA\Property(property: 'm_name', type: 'string'),
                ]
            )
        ),
        tags: ['JobPosition'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Zaktualizowano'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function update(JobPosition $jobPosition, JobPositionRequest $request): JsonResponse
    {
        $this->jobPositionService->updateJobPosition($jobPosition, $request->all());

        return new JsonResponse(null, 204);
    }

    /**
     * @param JobPosition $jobPosition
     * @return JsonResponse
     */
    #[OA\Delete(
        path: '/api/job-position/{uuid}',
        summary: 'Usuwa stanowisko',
        security: [['sanctum' => []]],
        tags: ['JobPosition'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Usunięto'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function destroy(JobPosition $jobPosition): JsonResponse
    {
        $this->jobPositionService->deleteJobPosition($jobPosition);

        return new JsonResponse(null, 204);
    }

    /**
     * @param User $user
     * @param AssignJobPositionRequest $request
     * @return JsonResponse
     */
    #[OA\Patch(
        path: '/api/user/{uuid}/jobposition',
        summary: 'Przypisuje stanowiska do użytkownika',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['job_positions'],
                properties: [
                    new OA\Property(property: 'job_positions', type: 'array', items: new OA\Items(type: 'string', format: 'uuid')),
                ]
            )
        ),
        tags: ['JobPosition'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Przypisano'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function assignJobPosition(User $user, AssignJobPositionRequest $request): JsonResponse
    {
        $this->jobPositionService->assignJobPosition($user, $request->all());

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
        path: '/api/job-position/export',
        summary: 'Eksport stanowisk do pliku',
        security: [['sanctum' => []]],
        tags: ['JobPosition'],
        parameters: [
            new OA\QueryParameter(name: 'type', required: true, schema: new OA\Schema(type: 'string', enum: ['xlsx', 'csv', 'ods'])),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Plik do pobrania', content: new OA\MediaType(mediaType: 'application/octet-stream')),
        ]
    )]
    public function export(ExportRequest $request): BinaryFileResponse
    {
        return $this->jobPositionService->export($request);
    }
}
