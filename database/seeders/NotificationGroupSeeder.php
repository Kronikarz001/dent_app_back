<?php

namespace Database\Seeders;

use App\Models\NotificationGroup;
use App\Models\NotificationType;
use Illuminate\Database\Seeder;

/**
 * Summary of NotificationGroupSeeder
 */
class NotificationGroupSeeder extends Seeder
{
    /**
     * @return void
     */
    public function run(): void
    {
        $groups = [
            [
                'code' => 'messages',
                'display_name' => 'Wiadomości',
                'description' => 'Powiadomienia związane z wiadomościami.',
                'order' => 1,
                'types' => [
                    'message_received',
                ],
            ],
            [
                'code' => 'announcements',
                'display_name' => 'Ogłoszenia',
                'description' => 'Powiadomienia związane z ogłoszeniami.',
                'order' => 2,
                'types' => [
                    'announcement_published',
                ],
            ],
            [
                'code' => 'calendar',
                'display_name' => 'Kalendarz',
                'description' => 'Powiadomienia związane z wydarzeniami w kalendarzu.',
                'order' => 3,
                'types' => [
                    'calendar_event_created',
                    'calendar_event_updated',
                    'calendar_event_reminder',
                ],
            ],
            [
                'code' => 'patients',
                'display_name' => 'Pacjenci',
                'description' => 'Powiadomienia związane z pacjentami i badaniami.',
                'order' => 4,
                'types' => [
                    'patient_created',
                    'dental_examination_created',
                ],
            ],
        ];

        foreach ($groups as $groupData) {
            $types = $groupData['types'];
            unset($groupData['types']);

            $group = NotificationGroup::updateOrCreate(
                ['code' => $groupData['code']],
                $groupData
            );

            NotificationType::whereIn('code', $types)
                ->update(['notification_group_uuid' => $group->uuid]);
        }
    }
}
