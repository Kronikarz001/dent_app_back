<?php

namespace App\Http\Controllers;

use App\Http\Requests\MessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Message;
use App\Models\User;
use App\Services\MessageServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;

/**
 * Summary of MessageController
 */
class MessageController extends Controller
{
    /**
     * @param MessageServiceInterface $messageService
     */
    public function __construct(
        private readonly MessageServiceInterface $messageService
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/message',
        summary: 'Skrzynka wiadomości zalogowanego użytkownika (odebrane, wysłane, broadcasty)',
        security: [['sanctum' => []]],
        tags: ['Message'],
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
        ]
    )]
    public function index(): LengthAwarePaginator
    {
        return $this->messageService->getInbox();
    }

    /**
     * @param User $user
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/message/user/{user}',
        summary: 'Pobiera prywatne i grupowe wiadomości danego użytkownika',
        security: [['sanctum' => []]],
        tags: ['Message'],
        parameters: [
            new OA\PathParameter(name: 'user', schema: new OA\Schema(type: 'string', format: 'uuid')),
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
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function indexAllForUser(User $user): LengthAwarePaginator
    {
        return $this->messageService->getAllMessageForUser($user);
    }

    /**
     * @param MessageRequest $request
     * @return MessageResource
     */
    #[OA\Post(
        path: '/api/message',
        summary: 'Wysyła wiadomość do konkretnej osoby lub do grupy',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['message'],
                properties: [
                    new OA\Property(property: 'message', type: 'string'),
                    new OA\Property(property: 'recipient_uuid', description: 'Wiadomość bezpośrednia do użytkownika (wyklucza message_group_uuid)', type: 'string', format: 'uuid', nullable: true),
                    new OA\Property(property: 'message_group_uuid', description: 'Wiadomość do grupy (wyklucza recipient_uuid)', type: 'string', format: 'uuid', nullable: true),
                ]
            )
        ),
        tags: ['Message'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Utworzono',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/MessageResource')]
                )
            ),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function store(MessageRequest $request): MessageResource
    {
        return new MessageResource($this->messageService->send($request->all()));
    }

    /**
     * @param Message $message
     * @return void
     */
    #[OA\Delete(
        path: '/api/message/{message}',
        summary: 'Usuwa wiadomość',
        security: [['sanctum' => []]],
        tags: ['Message'],
        parameters: [
            new OA\PathParameter(name: 'message', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Usunięto'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function destroy(Message $message): void
    {
        $this->messageService->deleteMessage($message);
    }
}
