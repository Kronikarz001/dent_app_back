<?php

namespace App\Http\Controllers;

use App\Dto\DailyHoursDto;
use App\Dto\StatisticUserDto;
use App\Http\Requests\StatisticsPeriodRequest;
use App\Http\Resources\DailyHoursResource;
use App\Http\Resources\HoursDistributionResource;
use App\Http\Resources\HoursSummaryResource;
use App\Http\Resources\StatisticUserResource;
use App\Models\User;
use App\Services\StatisticsServiceInterface;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;

/**
 * Summary of StatisticsController
 */
class StatisticsController extends Controller
{
    /**
     * @param StatisticsServiceInterface $statisticsService
     */
    public function __construct(
        private readonly StatisticsServiceInterface $statisticsService
    ) {}

    /**
     * @param StatisticsPeriodRequest $request
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/statistics/users',
        summary: 'Godziny pracy, urlopu i L4 pracowników w danym miesiącu (paginacja)',
        security: [['sanctum' => []]],
        tags: ['Statistics'],
        parameters: [
            new OA\QueryParameter(name: 'year', required: false, schema: new OA\Schema(type: 'integer', example: 2026)),
            new OA\QueryParameter(name: 'month', required: false, schema: new OA\Schema(type: 'integer', example: 11)),
            new OA\QueryParameter(name: 'searchString', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/StatisticUserResource')),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function users(StatisticsPeriodRequest $request): LengthAwarePaginator
    {
        return $this->statisticsService->getUsersStatistics($request->year(), $request->month())
            ->through(fn (StatisticUserDto $dto) => new StatisticUserResource($dto));
    }

    /**
     * @param User $user
     * @param StatisticsPeriodRequest $request
     * @return HoursSummaryResource
     */
    #[OA\Get(
        path: '/api/statistics/users/{uuid}/summary',
        summary: 'Suma godzin pracownika w miesiącu wg typu',
        security: [['sanctum' => []]],
        tags: ['Statistics'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\QueryParameter(name: 'year', required: false, schema: new OA\Schema(type: 'integer', example: 2026)),
            new OA\QueryParameter(name: 'month', required: false, schema: new OA\Schema(type: 'integer', example: 11)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(ref: '#/components/schemas/HoursSummaryResource')),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function summary(User $user, StatisticsPeriodRequest $request): HoursSummaryResource
    {
        return new HoursSummaryResource($this->statisticsService->getUserSummary($user, $request->year(), $request->month()));
    }

    /**
     * @param User $user
     * @param StatisticsPeriodRequest $request
     * @return AnonymousResourceCollection
     */
    #[OA\Get(
        path: '/api/statistics/users/{uuid}/distribution',
        summary: 'Rozkład procentowy godzin pracownika w miesiącu',
        security: [['sanctum' => []]],
        tags: ['Statistics'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\QueryParameter(name: 'year', required: false, schema: new OA\Schema(type: 'integer', example: 2026)),
            new OA\QueryParameter(name: 'month', required: false, schema: new OA\Schema(type: 'integer', example: 11)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/HoursDistributionResource'))
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function distribution(User $user, StatisticsPeriodRequest $request): AnonymousResourceCollection
    {
        return HoursDistributionResource::collection($this->statisticsService->getUserDistribution($user, $request->year(), $request->month()));
    }

    /**
     * @param User $user
     * @param StatisticsPeriodRequest $request
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/statistics/users/{uuid}/daily',
        summary: 'Dzienne wpisy godzinowe pracownika w miesiącu (paginacja)',
        security: [['sanctum' => []]],
        tags: ['Statistics'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\QueryParameter(name: 'year', required: false, schema: new OA\Schema(type: 'integer', example: 2026)),
            new OA\QueryParameter(name: 'month', required: false, schema: new OA\Schema(type: 'integer', example: 11)),
            new OA\QueryParameter(name: 'page', required: false, schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\QueryParameter(name: 'perPage', required: false, schema: new OA\Schema(type: 'integer', example: 50)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/DailyHoursResource')),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function daily(User $user, StatisticsPeriodRequest $request): LengthAwarePaginator
    {
        return $this->statisticsService->getUserDailyHours($user, $request->year(), $request->month(), $request->page(), $request->perPage())
            ->through(fn (DailyHoursDto $dto) => new DailyHoursResource($dto));
    }
}
