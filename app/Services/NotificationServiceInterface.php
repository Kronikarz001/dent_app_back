<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Notifications\Notification;

/**
 * Summary of NotificationServiceInterface
 */
interface NotificationServiceInterface
{
    /**
     * @param string $userUuid
     * @param bool $usePagination
     * @param int $perPage
     * @return array
     */
    public function getAllNotifications(string $userUuid, bool $usePagination = false, int $perPage = 15): array;

    /**
     * @param string $userUuid
     * @param string|null $notificationUuid
     * @return int
     */
    public function markAsRead(string $userUuid, ?string $notificationUuid = null): int;

    /**
     * @param User $user
     * @param Notification $notification
     * @return void
     */
    public function notify(User $user, Notification $notification): void;

    /**
     * @param string $userUuid
     * @return array
     */
    public function getUserNotificationPreferences(string $userUuid): array;

    /**
     * @param string $userUuid
     * @param array $preferences
     * @return void
     */
    public function updateUserNotificationPreferences(string $userUuid, array $preferences): void;
}
