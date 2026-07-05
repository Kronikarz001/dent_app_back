<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnnouncementRequest;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use App\Services\AnnouncementServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;

/**
 * Summary of AnnouncementController
 */
class AnnouncementController extends Controller
{
    /**
     * @param AnnouncementServiceInterface $announcementService
     */
    public function __construct(
        private readonly AnnouncementServiceInterface $announcementService
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/announcement',
        summary: 'Pobiera listę ogłoszeń (jedno na dzień, dla wszystkich)',
        security: [['sanctum' => []]],
        tags: ['Announcement'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/AnnouncementResource')),
                        ]),
                    ]
                )
            ),
        ]
    )]
    public function index(): LengthAwarePaginator
    {
        return $this->announcementService->getAll();
    }

    /**
     * @param AnnouncementRequest $request
     * @return AnnouncementResource
     */
    #[OA\Post(
        path: '/api/announcement',
        summary: 'Tworzy nowe ogłoszenie (jedno na dany dzień)',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'content', 'published_at'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Spotkanie zespołu'),
                    new OA\Property(property: 'content', type: 'string', example: 'Spotkanie odbędzie się o 10:00.'),
                    new OA\Property(property: 'published_at', type: 'string', format: 'date', example: '2026-07-01'),
                ]
            )
        ),
        tags: ['Announcement'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Utworzono',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/AnnouncementResource')]
                )
            ),
            new OA\Response(response: 422, description: 'Błąd walidacji (np. ogłoszenie na ten dzień już istnieje)'),
        ]
    )]
    public function store(AnnouncementRequest $request): AnnouncementResource
    {
        return new AnnouncementResource($this->announcementService->create($request->validated()));
    }

    /**
     * @param Announcement $announcement
     * @return AnnouncementResource
     */
    #[OA\Get(
        path: '/api/announcement/{uuid}',
        summary: 'Pobiera szczegóły ogłoszenia',
        security: [['sanctum' => []]],
        tags: ['Announcement'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/AnnouncementResource')]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function show(Announcement $announcement): AnnouncementResource
    {
        return new AnnouncementResource($announcement->load('author'));
    }

    /**
     * @param Announcement $announcement
     * @param AnnouncementRequest $request
     * @return AnnouncementResource
     */
    #[OA\Put(
        path: '/api/announcement/{uuid}',
        summary: 'Aktualizuje ogłoszenie',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'content', 'published_at'],
                properties: [
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(property: 'content', type: 'string'),
                    new OA\Property(property: 'published_at', type: 'string', format: 'date'),
                ]
            )
        ),
        tags: ['Announcement'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/AnnouncementResource')]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function update(Announcement $announcement, AnnouncementRequest $request): AnnouncementResource
    {
        return new AnnouncementResource($this->announcementService->update($announcement, $request->validated()));
    }

    /**
     * @param Announcement $announcement
     * @return JsonResponse
     */
    #[OA\Delete(
        path: '/api/announcement/{uuid}',
        summary: 'Usuwa ogłoszenie',
        security: [['sanctum' => []]],
        tags: ['Announcement'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Usunięto'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function destroy(Announcement $announcement): JsonResponse
    {
        $this->announcementService->delete($announcement);

        return new JsonResponse(null, 204);
    }
}
