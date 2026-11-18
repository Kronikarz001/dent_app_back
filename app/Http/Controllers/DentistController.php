<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Http\Resources\UserSelectResource;
use App\Models\Dentist;
use App\Services\DentistServiceInterface;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;

/**
 * Summary of DentistController
 */
class DentistController extends Controller
{
    /**
     * @param DentistServiceInterface $dentistService
     */
    public function __construct(
        private readonly DentistServiceInterface $dentistService
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/dentist',
        summary: 'Lista użytkowników ze stanowiskiem lekarza stomatologa (paginacja)',
        security: [['sanctum' => []]],
        tags: ['Dentist'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/UserResource')),
                        ]),
                    ]
                )
            ),
        ]
    )]
    public function index(): LengthAwarePaginator
    {
        return $this->dentistService->getDentists();
    }

    /**
     * @return AnonymousResourceCollection
     */
    #[OA\Get(
        path: '/api/dentist/selectlist',
        summary: 'Lista lekarzy stomatologów do selecta',
        security: [['sanctum' => []]],
        tags: ['Dentist'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/UserSelectResource'))
            ),
        ]
    )]
    public function selectList(): AnonymousResourceCollection
    {
        return UserSelectResource::collection($this->dentistService->getDentistsList());
    }

    /**
     * @param Dentist $dentist
     * @return UserResource
     */
    #[OA\Get(
        path: '/api/dentist/{uuid}',
        summary: 'Pobiera jednego lekarza stomatologa',
        security: [['sanctum' => []]],
        tags: ['Dentist'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/UserResource')]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function show(Dentist $dentist): UserResource
    {
        return new UserResource($dentist);
    }
}
