<?php

namespace App\Repositories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Summary of NotificationRepository
 */
class NotificationRepository
{
    /**
     * @param Notification $model
     */
    public function __construct(
        private Notification $model
    ) {}

    /**
     * @param string $userUuid
     * @param bool $usePagination
     * @param int $perPage
     * @return LengthAwarePaginator|Collection
     */
    public function getAllForUser(string $userUuid, bool $usePagination = false, int $perPage = 15): LengthAwarePaginator|Collection
    {
        $query = $this->model->query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $userUuid)
            ->orderBy('created_at', 'desc');

        return $usePagination
            ? $query->paginate($perPage)
            : $query->get();
    }

    /**
     * @param string $userUuid
     * @return int
     */
    public function getUnreadCount(string $userUuid): int
    {
        return $this->model->query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $userUuid)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * @param string $userUuid
     * @param string|null $notificationUuid
     * @return void
     */
    public function markAsRead(string $userUuid, ?string $notificationUuid = null): void
    {
        $query = $this->model->query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $userUuid)
            ->whereNull('read_at');

        if ($notificationUuid !== null) {
            $query->where('uuid', $notificationUuid);
        }

        $query->update(['read_at' => now()]);
    }
}
