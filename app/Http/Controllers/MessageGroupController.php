<?php

namespace App\Http\Controllers;

use App\Http\Requests\MessageGroupStoreRequest;
use App\Http\Requests\MessageGroupUpdateRequest;
use App\Http\Resources\MessageGroupResource;
use App\Models\MessageGroup;
use App\Models\User;
use App\Services\MessageGroupServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;

/**
 * Summary of MessageGroupController
 */
class MessageGroupController extends Controller
{
    /**
     * @param MessageGroupServiceInterface $messageGroupService
     */
    public function __construct(
        private readonly MessageGroupServiceInterface $messageGroupService,
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/message-group',
        summary: 'Pobiera grupy wiadomości zalogowanego użytkownika',
        security: [['sanctum' => []]],
        tags: ['MessageGroup'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/MessageGroupResource')),
                        ]),
                    ]
                )
            ),
        ]
    )]
    public function index(): LengthAwarePaginator
    {
        return $this->messageGroupService->getMyGroups();
    }

    /**
     * @param MessageGroupStoreRequest $request
     * @return MessageGroupResource
     */
    #[OA\Post(
        path: '/api/message-group',
        summary: 'Tworzy nową grupę wiadomości',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'user_uuids'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Zespół'),
                    new OA\Property(property: 'user_uuids', type: 'array', items: new OA\Items(type: 'string', format: 'uuid'), description: 'Min. jeden użytkownik (inny niż twórca)'),
                ]
            )
        ),
        tags: ['MessageGroup'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Utworzono',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/MessageGroupResource')]
                )
            ),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function store(MessageGroupStoreRequest $request): MessageGroupResource
    {
        return new MessageGroupResource($this->messageGroupService->create($request->validated()));
    }

    /**
     * @param MessageGroup $messageGroup
     * @return MessageGroupResource
     */
    #[OA\Get(
        path: '/api/message-group/{uuid}',
        summary: 'Pobiera szczegóły grupy wiadomości wraz z członkami',
        security: [['sanctum' => []]],
        tags: ['MessageGroup'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/MessageGroupResource')]
                )
            ),
            new OA\Response(response: 403, description: 'Nie jesteś członkiem tej grupy'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function show(MessageGroup $messageGroup): MessageGroupResource
    {
        return new MessageGroupResource($this->messageGroupService->getGroup($messageGroup));
    }

    /**
     * @param MessageGroup $messageGroup
     * @param MessageGroupUpdateRequest $request
     * @return MessageGroupResource
     */
    #[OA\Put(
        path: '/api/message-group/{uuid}',
        summary: 'Zmienia nazwę grupy wiadomości',
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
        tags: ['MessageGroup'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/MessageGroupResource')]
                )
            ),
            new OA\Response(response: 403, description: 'Nie jesteś członkiem tej grupy lub próbujesz edytować grupę domyślną'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function update(MessageGroup $messageGroup, MessageGroupUpdateRequest $request): MessageGroupResource
    {
        return new MessageGroupResource($this->messageGroupService->update($messageGroup, $request->validated()));
    }

    /**
     * @param MessageGroup $messageGroup
     * @return JsonResponse
     */
    #[OA\Delete(
        path: '/api/message-group/{uuid}',
        summary: 'Usuwa grupę wiadomości',
        security: [['sanctum' => []]],
        tags: ['MessageGroup'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Usunięto'),
            new OA\Response(response: 403, description: 'Nie jesteś członkiem tej grupy lub próbujesz usunąć grupę domyślną'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function destroy(MessageGroup $messageGroup): JsonResponse
    {
        $this->messageGroupService->delete($messageGroup);

        return new JsonResponse(null, 204);
    }

    /**
     * @param MessageGroup $messageGroup
     * @param User $user
     * @return MessageGroupResource
     */
    #[OA\Post(
        path: '/api/message-group/{uuid}/users/{userUuid}',
        summary: 'Dodaje użytkownika do grupy',
        security: [['sanctum' => []]],
        tags: ['MessageGroup'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\PathParameter(name: 'userUuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/MessageGroupResource')]
                )
            ),
            new OA\Response(response: 403, description: 'Nie jesteś członkiem tej grupy lub próbujesz edytować grupę domyślną'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function addUser(MessageGroup $messageGroup, User $user): MessageGroupResource
    {
        $this->messageGroupService->addUser($messageGroup, $user);

        return new MessageGroupResource($messageGroup->load(['creator', 'users']));
    }

    /**
     * @param MessageGroup $messageGroup
     * @param User $user
     * @return JsonResponse
     */
    #[OA\Delete(
        path: '/api/message-group/{uuid}/users/{userUuid}',
        summary: 'Usuwa użytkownika z grupy (min. 2 członków musi pozostać)',
        security: [['sanctum' => []]],
        tags: ['MessageGroup'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\PathParameter(name: 'userUuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Usunięto'),
            new OA\Response(response: 403, description: 'Nie jesteś członkiem tej grupy, grupa jest domyślna, lub grupa miałaby mniej niż 2 członków'),
            new OA\Response(response: 404, description: 'Nie znaleziono lub użytkownik nie jest członkiem grupy'),
        ]
    )]
    public function removeUser(MessageGroup $messageGroup, User $user): JsonResponse
    {
        $this->messageGroupService->removeUser($messageGroup, $user);

        return new JsonResponse(null, 204);
    }

    /**
     * @param MessageGroup $messageGroup
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/message-group/{uuid}/messages',
        summary: 'Pobiera wiadomości należące do grupy wraz z załączonymi plikami',
        security: [['sanctum' => []]],
        tags: ['MessageGroup'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/MessageResource')),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Nie jesteś członkiem tej grupy'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function messages(MessageGroup $messageGroup): LengthAwarePaginator
    {
        return $this->messageGroupService->getGroupMessages($messageGroup);
    }

    /**
     * @param MessageGroup $messageGroup
     * @return JsonResponse
     */
    #[OA\Post(
        path: '/api/message-group/{uuid}/read',
        summary: 'Oznacza grupę jako przeczytaną przez zalogowanego użytkownika',
        security: [['sanctum' => []]],
        tags: ['MessageGroup'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Oznaczono'),
            new OA\Response(response: 403, description: 'Nie jesteś członkiem tej grupy'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function markAsRead(MessageGroup $messageGroup): JsonResponse
    {
        $this->messageGroupService->markGroupAsRead($messageGroup);

        return new JsonResponse(null, 204);
    }
}
