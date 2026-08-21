<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\RegistersRoutePermissions;
use Illuminate\Database\Seeder;

/**
 * Summary of RegisterExistingRoutePermissionsSeeder
 *
 * Wzorcowy sposób rejestrowania route'ów pod system uprawnień: dla każdego
 * zasobu (klucz) wypisujemy nazwy route'ów, które do niego należą.
 * `{resource}.view`/`{resource}.edit` powstają automatycznie, w zależności
 * od czasownika HTTP danego route'a. `auth.login`, `user.forgot_password`,
 * `user.reset_password` są celowo pominięte (publiczne, bez auth:sanctum),
 * `auth.logout` jest zawsze dozwolony (patrz PermissionMiddleware::ALWAYS_ALLOWED).
 */
class RegisterExistingRoutePermissionsSeeder extends Seeder
{
    use RegistersRoutePermissions;

    /**
     * @return array<string, string[]>
     */
    protected function resourceRoutes(): array
    {
        return [
            'announcement' => [
                'announcement.index',
                'announcement.store',
                'announcement.show',
                'announcement.update',
                'announcement.destroy',
            ],
            'calendar' => [
                'calendar.index',
                'calendar.store',
                'calendar.export',
                'calendar.selectList',
                'calendar.show',
                'calendar.update',
                'calendar.destroy',
                'calendar.assignUsers',
                'calendar.history',
                'calendar.history.export',
            ],
            'calendarfile' => [
                'calendarfile.index',
                'calendarfile.store',
                'calendarfile.download',
                'calendarfile.newversion',
                'calendarfile.show',
                'calendarfile.update',
                'calendarfile.destroy',
            ],
            'company' => [
                'company.index',
                'company.store',
                'company.show',
                'company.update',
                'company.destroy',
            ],
            'dentalExamination' => [
                'dentalExamination.index',
                'dentalExamination.store',
                'dentalExamination.export',
                'dentalExamination.selectList',
                'dentalExamination.show',
                'dentalExamination.update',
                'dentalExamination.destroy',
                'dentalExamination.history',
                'dentalExamination.history.export',
            ],
            'dentalexaminationfile' => [
                'dentalexaminationfile.index',
                'dentalexaminationfile.store',
                'dentalexaminationfile.download',
                'dentalexaminationfile.newversion',
                'dentalexaminationfile.show',
                'dentalexaminationfile.update',
                'dentalexaminationfile.destroy',
            ],
            'jobPosition' => [
                'jobPosition.index',
                'jobPosition.store',
                'jobPosition.export',
                'jobPosition.selectList',
                'jobPosition.show',
                'jobPosition.update',
                'jobPosition.destroy',
                'jobPosition.history',
                'jobPosition.history.export',
            ],
            'jobpositionfile' => [
                'jobpositionfile.index',
                'jobpositionfile.store',
                'jobpositionfile.download',
                'jobpositionfile.newversion',
                'jobpositionfile.show',
                'jobpositionfile.update',
                'jobpositionfile.destroy',
            ],
            'material' => [
                'material.index',
                'material.store',
                'material.export',
                'material.selectList',
                'material.show',
                'material.update',
                'material.destroy',
                'material.history',
                'material.history.export',
            ],
            'materialfile' => [
                'materialfile.index',
                'materialfile.store',
                'materialfile.download',
                'materialfile.newversion',
                'materialfile.show',
                'materialfile.update',
                'materialfile.destroy',
            ],
            'message' => [
                'message.index',
                'message.store',
                'message.unreadCount',
                'message.destroy',
                'message.markAsRead',
            ],
            'messageGroup' => [
                'messageGroup.index',
                'messageGroup.store',
                'messageGroup.show',
                'messageGroup.update',
                'messageGroup.destroy',
                'messageGroup.messages',
                'messageGroup.markAsRead',
                'messageGroup.addUser',
                'messageGroup.removeUser',
            ],
            'messagefile' => [
                'messagefile.index',
                'messagefile.store',
                'messagefile.download',
                'messagefile.show',
                'messagefile.update',
                'messagefile.destroy',
            ],
            'notifications' => [
                'notifications.index',
                'notifications.mark-as-read',
                'notifications.preferences.index',
                'notifications.preferences.update',
            ],
            'patient' => [
                'patient.index',
                'patient.store',
                'patient.export',
                'patient.selectList',
                'patient.show',
                'patient.update',
                'patient.destroy',
                'patient.history',
                'patient.history.export',
            ],
            'patientfile' => [
                'patientfile.index',
                'patientfile.store',
                'patientfile.download',
                'patientfile.newversion',
                'patientfile.show',
                'patientfile.update',
                'patientfile.destroy',
            ],
            'user' => [
                'user.index',
                'user.store',
                'user.edit_password',
                'user.export',
                'user.selectList',
                'user.user-info',
                'user.show',
                'user.update',
                'user.destroy',
                'user.jobPosition.assignJobPosition',
                'user.history',
                'user.history.export',
            ],
            'userfile' => [
                'userfile.avatar-store',
                'userfile.background-store',
                'userfile.index',
                'userfile.store',
                'userfile.avatar-download',
                'userfile.background-download',
                'userfile.download',
                'userfile.newversion',
                'userfile.show',
                'userfile.update',
                'userfile.destroy',
            ],
        ];
    }
}
