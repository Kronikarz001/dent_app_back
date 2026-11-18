<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;

/**
 * Summary of PermissionRoute
 *
 * @property string $route_name
 * @property string $resource
 */
class PermissionRoute extends UuidModel
{
    public const CACHE_KEY = 'permission_routes.resources';

    protected $fillable = [
        'route_name',
        'resource',
    ];

    /**
     * @return void
     */
    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }
}
