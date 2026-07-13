<?php

namespace App\Repositories;

use App\Models\NotificationChannel;
use App\Models\NotificationGroup;
use App\Models\NotificationPreference;
use Illuminate\Support\Collection;

/**
 * Summary of NotificationPreferenceRepository
 */
class NotificationPreferenceRepository
{
    /**
     * @param NotificationPreference $model
     */
    public function __construct(
        private NotificationPreference $model
    ) {}

    /**
     * @param string $userUuid
     * @return Collection
     */
    public function getUserPreferences(string $userUuid): Collection
    {
        return $this->model->query()
            ->where('user_uuid', $userUuid)
            ->get()
            ->keyBy(fn (NotificationPreference $preference) => $preference->notification_type_uuid.':'.$preference->notification_channel_uuid);
    }

    /**
     * @return Collection
     */
    public function getGroupsWithTypes(): Collection
    {
        return NotificationGroup::with('notificationTypes')
            ->orderBy('order')
            ->orderBy('display_name')
            ->get();
    }

    /**
     * @return Collection
     */
    public function getConfigurableChannels(): Collection
    {
        return NotificationChannel::query()
            ->where('is_configurable', true)
            ->where('is_internal', false)
            ->get();
    }

    /**
     * @param string $userUuid
     * @param string $typeUuid
     * @param string $channelUuid
     * @param bool $enabled
     * @return NotificationPreference
     */
    public function updateOrCreate(string $userUuid, string $typeUuid, string $channelUuid, bool $enabled): NotificationPreference
    {
        return $this->model->updateOrCreate(
            [
                'user_uuid' => $userUuid,
                'notification_type_uuid' => $typeUuid,
                'notification_channel_uuid' => $channelUuid,
            ],
            [
                'enabled' => $enabled,
            ]
        );
    }
}
