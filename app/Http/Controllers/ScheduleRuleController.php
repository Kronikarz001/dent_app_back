<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScheduleRuleRequest;
use App\Http\Resources\ScheduleRuleResource;
use App\Models\ScheduleRule;
use App\Services\ScheduleRuleServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;

/**
 * Summary of ScheduleRuleController
 */
class ScheduleRuleController extends Controller
{
    /**
     * @param ScheduleRuleServiceInterface $scheduleRuleService
     */
    public function __construct(
        private readonly ScheduleRuleServiceInterface $scheduleRuleService
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/schedule-rule',
        summary: 'Stałe reguły tygodniowego grafiku zalogowanego pracownika',
        security: [['sanctum' => []]],
        tags: ['ScheduleRule'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ScheduleRuleResource')),
                        ]),
                    ]
                )
            ),
        ]
    )]
    public function index(): LengthAwarePaginator
    {
        return $this->scheduleRuleService->getMyRules();
    }

    /**
     * @param ScheduleRule $scheduleRule
     * @return ScheduleRuleResource
     */
    #[OA\Get(
        path: '/api/schedule-rule/{uuid}',
        summary: 'Pobiera jedną regułę zalogowanego pracownika',
        security: [['sanctum' => []]],
        tags: ['ScheduleRule'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(ref: '#/components/schemas/ScheduleRuleResource')
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function show(ScheduleRule $scheduleRule): ScheduleRuleResource
    {
        return new ScheduleRuleResource($this->scheduleRuleService->getMyRule($scheduleRule));
    }

    /**
     * @param ScheduleRuleRequest $request
     * @return ScheduleRuleResource
     */
    #[OA\Post(
        path: '/api/schedule-rule',
        summary: 'Dodaje regułę tygodniowego grafiku zalogowanemu pracownikowi',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(ref: '#/components/requestBodies/ScheduleRuleRequest'),
        tags: ['ScheduleRule'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Utworzono',
                content: new OA\JsonContent(ref: '#/components/schemas/ScheduleRuleResource')
            ),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function store(ScheduleRuleRequest $request): ScheduleRuleResource
    {
        return new ScheduleRuleResource($this->scheduleRuleService->createRule($request->validated()));
    }

    /**
     * @param ScheduleRule $scheduleRule
     * @param ScheduleRuleRequest $request
     * @return JsonResponse
     */
    #[OA\Put(
        path: '/api/schedule-rule/{uuid}',
        summary: 'Aktualizuje regułę zalogowanego pracownika',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(ref: '#/components/requestBodies/ScheduleRuleRequest'),
        tags: ['ScheduleRule'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Zaktualizowano'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function update(ScheduleRule $scheduleRule, ScheduleRuleRequest $request): JsonResponse
    {
        $this->scheduleRuleService->updateRule($scheduleRule, $request->validated());

        return new JsonResponse(null, 204);
    }

    /**
     * @param ScheduleRule $scheduleRule
     * @return JsonResponse
     */
    #[OA\Delete(
        path: '/api/schedule-rule/{uuid}',
        summary: 'Usuwa regułę zalogowanego pracownika',
        security: [['sanctum' => []]],
        tags: ['ScheduleRule'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Usunięto'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function destroy(ScheduleRule $scheduleRule): JsonResponse
    {
        $this->scheduleRuleService->deleteRule($scheduleRule);

        return new JsonResponse(null, 204);
    }
}
