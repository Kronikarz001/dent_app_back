<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Summary of Company
 *
 * @property string $uuid
 * @property string $name
 * @property string $regon
 * @property string $nip
 * @property string $address
 * @property string $province
 * @property string $district
 * @property string $municipality
 * @property string|null $business_form
 * @property string|null $type_of_business
 * @property string|null $form_of_ownership
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Company extends UuidModel
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'name',
        'regon',
        'nip',
        'address',
        'province',
        'district',
        'municipality',
        'business_form',
        'type_of_business',
        'form_of_ownership',
    ];
}
