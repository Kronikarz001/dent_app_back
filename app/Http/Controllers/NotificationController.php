<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateNotificationPreferencesRequest;
use App\Http\Resources\NotificationResource;
use App\Services\NotificationServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/**
 * Summary of NotificationController
 */
class NotificationController extends Controller
{
    /**
     * @param NotificationServiceInterface $notificationService
     */
    public function __construct(
        private readonly NotificationServiceInterface $notificationService
    ) {}

    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[OA\Get(
        path: '/api/notifications',
        summary: 'Pobiera powiadomienia zalogowanego użytkownika wraz z licznikiem nieprzeczytanych',
        security: [['sanctum' => []]],
        tags: ['Notification'],
        parameters: [
            new OA\QueryParameter(name: 'page', schema: new OA\Schema(type: 'integer'), required: false),
            new OA\QueryParameter(name: 'per_page', schema: new OA\Schema(type: 'integer', default: 15), required: false),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'notifications', type: 'array', items: new OA\Items(ref: '#/components/schemas/NotificationResource')),
                        new OA\Property(property: 'unread_count', type: 'integer'),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true),
                    ]
                )
            ),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $usePagination = $request->has('page') || $request->has('per_page');

        $result = $this->notificationService->getAllNotifications(
            userUuid: auth()->id(),
            usePagination: $usePagination,
            perPage: (int) $request->input('per_page', 15)
        );

        $notifications = $result['notifications'];

        return response()->json([
            'notifications' => NotificationResource::collection($notifications),
            'unread_count' => $result['unread_count'],
            'pagination' => $usePagination ? [
                'total' => $notifications->total(),
                'per_page' => $notifications->perPage(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'from' => $notifications->firstItem(),
                'to' => $notifications->lastItem(),
            ] : null,
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[OA\Post(
        path: '/api/notifications/mark-as-read',
        summary: 'Oznacza powiadomienie (lub wszystkie) jako przeczytane',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'notification_uuid', type: 'string', format: 'uuid', nullable: true),
                ]
            )
        ),
        tags: ['Notification'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'unread_count', type: 'integer'),
                    ]
                )
            ),
        ]
    )]
    public function markAsRead(Request $request): JsonResponse
    {
        $notificationUuid = $request->input('notification_uuid');

        $unreadCount = $this->notificationService->markAsRead(
            userUuid: auth()->id(),
            notificationUuid: $notificationUuid
        );

        return response()->json([
            'message' => $notificationUuid
                ? 'Powiadomienie zostało oznaczone jako przeczytane.'
                : 'Wszystkie powiadomienia zostały oznaczone jako przeczytane.',
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * @return JsonResponse
     */
    #[OA\Get(
        path: '/api/notifications/preferences',
        summary: 'Pobiera ustawienia powiadomień zalogowanego użytkownika',
        security: [['sanctum' => []]],
        tags: ['Notification'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'preferences', type: 'array', items: new OA\Items(type: 'object')),
                    ]
                )
            ),
        ]
    )]
    public function getPreferences(): JsonResponse
    {
        $preferences = $this->notificationService->getUserNotificationPreferences(auth()->id());

        return response()->json([
            'preferences' => $preferences,
        ]);
    }

    /**
     * @param UpdateNotificationPreferencesRequest $request
     * @return JsonResponse
     */
    #[OA\Put(
        path: '/api/notifications/preferences',
        summary: 'Aktualizuje ustawienia powiadomień zalogowanego użytkownika',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['preferences'],
                properties: [
                    new OA\Property(
                        property: 'preferences',
                        type: 'array',
                        items: new OA\Items(
                            required: ['type_id', 'channel_id', 'enabled'],
                            properties: [
                                new OA\Property(property: 'type_id', type: 'string', format: 'uuid'),
                                new OA\Property(property: 'channel_id', type: 'string', format: 'uuid'),
                                new OA\Property(property: 'enabled', type: 'boolean'),
                            ],
                            type: 'object'
                        )
                    ),
                ]
            )
        ),
        tags: ['Notification'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'message', type: 'string')]
                )
            ),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function updatePreferences(UpdateNotificationPreferencesRequest $request): JsonResponse
    {
        $this->notificationService->updateUserNotificationPreferences(
            auth()->id(),
            $request->validated()['preferences']
        );

        return response()->json([
            'message' => 'Ustawienia powiadomień zostały pomyślnie zaktualizowane.',
        ]);
    }
}
