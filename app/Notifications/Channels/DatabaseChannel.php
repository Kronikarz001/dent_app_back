<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Channels\DatabaseChannel as BaseDatabaseChannel;
use Illuminate\Notifications\Notification;

/**
 * Summary of DatabaseChannel
 */
class DatabaseChannel extends BaseDatabaseChannel
{
    /**
     * @param mixed $notifiable
     * @param Notification $notification
     * @return array
     */
    protected function buildPayload($notifiable, Notification $notification): array
    {
        $payload = parent::buildPayload($notifiable, $notification);

        $payload['uuid'] = $payload['id'];
        unset($payload['id']);

        if (method_exists($notification, 'notificationTypeUuid')) {
            $payload['notification_type_uuid'] = $notification->notificationTypeUuid($notifiable);
        }

        return $payload;
    }
}
