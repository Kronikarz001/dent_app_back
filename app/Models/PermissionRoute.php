<?php

namespace App\Models;

/**
 * Summary of PermissionRoute
 */
class PermissionRoute extends UuidModel
{
    protected $fillable = [
        'route_name',
        'resource',
    ];
}
