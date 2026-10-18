<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\RegistersRoutePermissions;
use Illuminate\Database\Seeder;

/**
 * Summary of RegisterPhotoMedicinePrescriptionRoutePermissionsSeeder
 */
class RegisterPhotoMedicinePrescriptionRoutePermissionsSeeder extends Seeder
{
    use RegistersRoutePermissions;

    /**
     * @return array<string, string[]>
     */
    protected function resourceRoutes(): array
    {
        return [
            'patientphoto' => [
                'patientphoto.index',
                'patientphoto.store',
                'patientphoto.show',
                'patientphoto.update',
                'patientphoto.destroy',
                'patientphoto.download',
                'patient.romexisSyncList',
            ],
            'medicine' => [
                'medicine.index',
                'medicine.store',
                'medicine.show',
                'medicine.update',
                'medicine.destroy',
                'medicine.selectList',
            ],
            'prescription' => [
                'prescription.index',
                'prescription.store',
                'prescription.show',
                'prescription.update',
                'prescription.destroy',
                'prescription.issue',
                'patient.prescriptions',
            ],
        ];
    }
}
