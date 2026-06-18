<?php

namespace App\Http\Controllers;

use App\Models\MessageGroup;
use App\Services\MessageServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;

/**
 * Summary of MessageGroupController
 */
class MessageGroupController extends Controller
{
    /**
     * @param MessageServiceInterface $messageService
     */
    public function __construct(
        private readonly MessageServiceInterface $messageService
    ) {}

    /**
     * @param MessageGroup $messageGroup
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/message-group/{uuid}/messages',
        summary: 'Pobiera konwersację (wiadomości) należące do danej grupy',
        security: [['sanctum' => []]],
        tags: ['Message'],
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
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function messages(MessageGroup $messageGroup): LengthAwarePaginator
    {
        return $this->messageService->getGroupMessages($messageGroup);
    }
}
