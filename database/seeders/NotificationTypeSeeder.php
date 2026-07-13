<?php

namespace Database\Seeders;

use App\Models\NotificationType;
use Illuminate\Database\Seeder;

/**
 * Summary of NotificationTypeSeeder
 */
class NotificationTypeSeeder extends Seeder
{
    /**
     * @return void
     */
    public function run(): void
    {
        $types = [
            [
                'code' => 'message_received',
                'display_name' => 'Nowa wiadomość',
                'description' => 'Powiadomienia o nowych wiadomościach skierowanych do Ciebie.',
            ],
            [
                'code' => 'announcement_published',
                'display_name' => 'Nowe ogłoszenie',
                'description' => 'Powiadomienia o opublikowaniu nowego ogłoszenia.',
            ],
            [
                'code' => 'calendar_event_created',
                'display_name' => 'Nowe wydarzenie w kalendarzu',
                'description' => 'Powiadomienia o utworzeniu wydarzenia, w którym uczestniczysz.',
            ],
            [
                'code' => 'calendar_event_updated',
                'display_name' => 'Zmiana wydarzenia w kalendarzu',
                'description' => 'Powiadomienia o zmianie wydarzenia, w którym uczestniczysz.',
            ],
            [
                'code' => 'calendar_event_reminder',
                'display_name' => 'Przypomnienie o wydarzeniu',
                'description' => 'Przypomnienia o zbliżających się wydarzeniach w kalendarzu.',
            ],
            [
                'code' => 'patient_created',
                'display_name' => 'Nowy pacjent',
                'description' => 'Powiadomienia o dodaniu nowego pacjenta.',
            ],
            [
                'code' => 'dental_examination_created',
                'display_name' => 'Nowe badanie stomatologiczne',
                'description' => 'Powiadomienia o utworzeniu nowego badania stomatologicznego.',
            ],
        ];

        foreach ($types as $type) {
            NotificationType::updateOrCreate(['code' => $type['code']], $type);
        }
    }
}
