<?php

namespace App\Services;

use App\Models\NotificationChannel;
use App\Models\NotificationType;
use App\Models\User;
use App\Repositories\NotificationPreferenceRepository;
use App\Repositories\NotificationRepository;
use Illuminate\Notifications\Notification;

/**
 * Summary of NotificationService
 */
readonly class NotificationService implements NotificationServiceInterface
{
    /**
     * @param NotificationRepository $repository
     * @param NotificationPreferenceRepository $preferenceRepository
     */
    public function __construct(
        private NotificationRepository $repository,
        private NotificationPreferenceRepository $preferenceRepository,
    ) {}

    /**
     * @param string $userUuid
     * @param bool $usePagination
     * @param int $perPage
     * @return array
     */
    public function getAllNotifications(string $userUuid, bool $usePagination = false, int $perPage = 15): array
    {
        return [
            'notifications' => $this->repository->getAllForUser($userUuid, $usePagination, $perPage),
            'unread_count' => $this->repository->getUnreadCount($userUuid),
        ];
    }

    /**
     * @param string $userUuid
     * @param string|null $notificationUuid
     * @return int
     */
    public function markAsRead(string $userUuid, ?string $notificationUuid = null): int
    {
        $this->repository->markAsRead($userUuid, $notificationUuid);

        return $this->repository->getUnreadCount($userUuid);
    }

    /**
     * @param User $user
     * @param Notification $notification
     * @return void
     */
    public function notify(User $user, Notification $notification): void
    {
        $user->notify($notification);
    }

    /**
     * Buduje strukturę preferencji powiadomień użytkownika pogrupowaną po grupach i typach.
     *
     * @param string $userUuid
     * @return array
     */
    public function getUserNotificationPreferences(string $userUuid): array
    {
        $groups = $this->preferenceRepository->getGroupsWithTypes();
        $channels = $this->preferenceRepository->getConfigurableChannels();
        $userPreferences = $this->preferenceRepository->getUserPreferences($userUuid);

        $preferences = [];

        foreach ($groups as $group) {
            $groupPreferences = [
                'group_id' => $group->uuid,
                'group_code' => $group->code,
                'group_name' => $group->display_name,
                'group_description' => $group->description,
                'notification_types' => [],
            ];

            foreach ($group->notificationTypes as $type) {
                $typePreferences = [
                    'type_id' => $type->uuid,
                    'type_code' => $type->code,
                    'type_name' => $type->display_name,
                    'type_description' => $type->description,
                    'channels' => [],
                ];

                foreach ($channels as $channel) {
                    $preferenceKey = $type->uuid.':'.$channel->uuid;
                    $preference = $userPreferences->get($preferenceKey);

                    $typePreferences['channels'][] = [
                        'channel_id' => $channel->uuid,
                        'channel_name' => $channel->name,
                        'channel_display_name' => $channel->display_name,
                        'enabled' => $preference ? $preference->enabled : true,
                    ];
                }

                $groupPreferences['notification_types'][] = $typePreferences;
            }

            $preferences[] = $groupPreferences;
        }

        return $preferences;
    }

    /**
     * Aktualizuje preferencje powiadomień użytkownika.
     *
     * @param string $userUuid
     * @param array $preferences tablica w formacie [['type_id' => '...', 'channel_id' => '...', 'enabled' => bool], ...]
     * @return void
     */
    public function updateUserNotificationPreferences(string $userUuid, array $preferences): void
    {
        foreach ($preferences as $preference) {
            $typeUuid = $preference['type_id'];
            $channelUuid = $preference['channel_id'];
            $enabled = $preference['enabled'];

            $channel = NotificationChannel::find($channelUuid);
            if (! $channel || ! $channel->is_configurable) {
                continue;
            }

            $type = NotificationType::find($typeUuid);
            if (! $type) {
                continue;
            }

            $this->preferenceRepository->updateOrCreate($userUuid, $typeUuid, $channelUuid, $enabled);
        }
    }
}
