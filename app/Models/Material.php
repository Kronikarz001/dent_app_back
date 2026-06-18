<?php

namespace App\Models;

use App\Traits\HasFile;
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
}
