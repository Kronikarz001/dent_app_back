<?php

namespace App\Models;

use App\Enums\ReimbursementLevel;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * Summary of Medicine
 *
 * @property string $uuid
 * @property string $name
 * @property string|null $description
 * @property string|null $ean_gtin
 * @property ReimbursementLevel $reimbursement_level
 * @property string|null $external_reference
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Medicine extends UuidModel
{
    use Auditable;

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'description',
        'ean_gtin',
        'reimbursement_level',
        'external_reference',
    ];

    /**
     * @return BelongsToMany
     */
    public function prescriptions(): BelongsToMany
    {
        return $this->belongsToMany(
            Prescription::class,
            'prescription_medicine',
            'medicine_uuid',
            'prescription_uuid',
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
            'reimbursement_level' => ReimbursementLevel::class,
        ];
    }
}
