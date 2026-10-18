<?php

namespace App\Models;

use App\Enums\PrescriptionStatus;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * Summary of Prescription
 *
 * @property string $uuid
 * @property string|null $name
 * @property string|null $code
 * @property PrescriptionStatus $status
 * @property string $patient_uuid
 * @property string $doctor_uuid
 * @property string|null $external_reference
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Patient $patient
 * @property-read User $doctor
 */
class Prescription extends UuidModel
{
    use Auditable;

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'code',
        'status',
        'patient_uuid',
        'doctor_uuid',
        'external_reference',
    ];

    /**
     * @return BelongsTo
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_uuid', 'uuid');
    }

    /**
     * @return BelongsTo
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_uuid', 'uuid');
    }

    /**
     * @return BelongsToMany
     */
    public function medicines(): BelongsToMany
    {
        return $this->belongsToMany(
            Medicine::class,
            'prescription_medicine',
            'prescription_uuid',
            'medicine_uuid',
            'uuid',
            'uuid'
        )->withPivot('quantity', 'dosage');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PrescriptionStatus::class,
        ];
    }
}
