<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Summary of GenericNotification
 */
class GenericNotification extends Notification
{
    /**
     * @param array $payload
     * @param string|null $notificationTypeUuid
     */
    public function __construct(
        private readonly array $payload,
        private readonly ?string $notificationTypeUuid = null,
    ) {}

    /**
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable): array
    {
        return $this->payload;
    }

    /**
     * @param mixed $notifiable
     * @return string
     */
    public function databaseType($notifiable): string
    {
        return $this->payload['type'] ?? static::class;
    }

    /**
     * @param mixed $notifiable
     * @return string|null
     */
    public function notificationTypeUuid($notifiable): ?string
    {
        return $this->notificationTypeUuid;
    }
}
