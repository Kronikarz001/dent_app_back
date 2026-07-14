<?php

namespace App\Http\Controllers;

use App\Enums\SearchModuleType;
use App\Http\Requests\GlobalSearchRequest;
use App\Services\GlobalSearchServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

/**
 * Summary of SearchController
 */
class SearchController extends Controller
{
    /**
     * @param GlobalSearchServiceInterface $searchService
     */
    public function __construct(
        private readonly GlobalSearchServiceInterface $searchService
    ) {}

    /**
     * @param GlobalSearchRequest $request
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/search',
        summary: 'Globalna szukajka po modułach z opcjonalnym filtrem modułów',
        security: [['sanctum' => []]],
        tags: ['Search'],
        parameters: [
            new OA\QueryParameter(name: 'searchString', schema: new OA\Schema(type: 'string'), required: false),
            new OA\QueryParameter(
                name: 'modules[]',
                description: 'Opcjonalny filtr modułów; brak = wszystkie moduły',
                schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
                required: false
            ),
            new OA\QueryParameter(name: 'perPage', schema: new OA\Schema(type: 'integer', default: -1), required: false),
            new OA\QueryParameter(name: 'page', schema: new OA\Schema(type: 'integer', default: 1), required: false),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(
                                property: 'data',
                                type: 'array',
                                items: new OA\Items(
                                    properties: [
                                        new OA\Property(property: 'name', type: 'string'),
                                        new OA\Property(property: 'description', type: 'string', nullable: true),
                                        new OA\Property(property: 'link', type: 'string', nullable: true),
                                    ],
                                    type: 'object'
                                )
                            ),
                        ]),
                    ]
                )
            ),
        ]
    )]
    public function index(GlobalSearchRequest $request): LengthAwarePaginator
    {
        return $this->searchService->search($request->input('modules'));
    }

    /**
     * @return JsonResponse
     */
    #[OA\Get(
        path: '/api/search/modules',
        summary: 'Zwraca listę modułów szukajki z polskimi nazwami dla filtra',
        security: [['sanctum' => []]],
        tags: ['Search'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'modules',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'value', type: 'string'),
                                    new OA\Property(property: 'label', type: 'string'),
                                ],
                                type: 'object'
                            )
                        ),
                    ]
                )
            ),
        ]
    )]
    public function modules(): JsonResponse
    {
        return response()->json([
            'modules' => SearchModuleType::options(),
        ]);
    }
}
