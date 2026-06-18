<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasFile;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * Summary of Material
 *
 * @property string $uuid
 * @property string $name
 * @property string|null $description
 * @property string|null $short_description
 * @property int|null $price
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Material extends UuidModel
{
    use Auditable, HasFile;

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
    public function dentalExaminations(): BelongsToMany
    {
        return $this->belongsToMany(
            DentalExamination::class,
            'dental_examinations_materials',
            'material_uuid',
            'dental_examination_uuid',
            'uuid',
            'uuid'
        );
    }
}
