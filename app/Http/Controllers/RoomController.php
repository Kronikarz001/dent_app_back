<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoomRequest;
use App\Http\Resources\RoomResource;
use App\Models\Room;
use App\Services\RoomServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;

/**
 * Summary of RoomController
 */
class RoomController extends Controller
{
    /**
     * @param RoomServiceInterface $roomService
     */
    public function __construct(
        private readonly RoomServiceInterface $roomService
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/room',
        summary: 'Lista gabinetów (paginacja)',
        security: [['sanctum' => []]],
        tags: ['Room'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/RoomResource')),
                        ]),
                    ]
                )
            ),
        ]
    )]
    public function index(): LengthAwarePaginator
    {
        return $this->roomService->getRooms();
    }

    /**
     * @return Collection
     */
    #[OA\Get(
        path: '/api/room/selectlist',
        summary: 'Aktywne gabinety do selecta (uuid + name, bez paginacji)',
        security: [['sanctum' => []]],
        tags: ['Room'],
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
        return $this->roomService->getRoomsList();
    }

    /**
     * @param Room $room
     * @return RoomResource
     */
    #[OA\Get(
        path: '/api/room/{uuid}',
        summary: 'Pobiera jeden gabinet',
        security: [['sanctum' => []]],
        tags: ['Room'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(ref: '#/components/schemas/RoomResource')
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function show(Room $room): RoomResource
    {
        return new RoomResource($room);
    }

    /**
     * @param RoomRequest $request
     * @return RoomResource
     */
    #[OA\Post(
        path: '/api/room',
        summary: 'Tworzy nowy gabinet',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(ref: '#/components/requestBodies/RoomRequest'),
        tags: ['Room'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Utworzono',
                content: new OA\JsonContent(ref: '#/components/schemas/RoomResource')
            ),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function store(RoomRequest $request): RoomResource
    {
        return new RoomResource($this->roomService->createRoom($request->validated()));
    }

    /**
     * @param Room $room
     * @param RoomRequest $request
     * @return JsonResponse
     */
    #[OA\Put(
        path: '/api/room/{uuid}',
        summary: 'Aktualizuje gabinet',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(ref: '#/components/requestBodies/RoomRequest'),
        tags: ['Room'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Zaktualizowano'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function update(Room $room, RoomRequest $request): JsonResponse
    {
        $this->roomService->updateRoom($room, $request->validated());

        return new JsonResponse(null, 204);
    }

    /**
     * @param Room $room
     * @return JsonResponse
     */
    #[OA\Delete(
        path: '/api/room/{uuid}',
        summary: 'Usuwa gabinet',
        security: [['sanctum' => []]],
        tags: ['Room'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Usunięto'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function destroy(Room $room): JsonResponse
    {
        $this->roomService->deleteRoom($room);

        return new JsonResponse(null, 204);
    }
}
