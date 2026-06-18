<?php

namespace App\Models;

use App\Traits\HasFile;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * Summary of DentalExamination
 *
 * @property string $uuid
 * @property string $name
 * @property string|null $description
 * @property string|null $short_description
 * @property int|null $price
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class DentalExamination extends UuidModel
{
    use HasFile;

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'description',
        'short_description',
        'price',
    ];

    /**
     * @return BelongsToMany
     */
    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(
            Material::class,
            'dental_examinations_materials',
            'dental_examination_uuid',
            'material_uuid',
            'uuid',
            'uuid'
        );
    }

    /**
     * @return BelongsToMany
     */
    public function calendars(): BelongsToMany
    {
        return $this->belongsToMany(
            Calendar::class,
            'calendars_dental_examinations',
            'dental_examination_uuid',
            'calendar_uuid',
            'uuid',
            'uuid'
        );
    }
}
