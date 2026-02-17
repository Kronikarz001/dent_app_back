<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property string $uuid
 */
abstract class UuidModel
{
    /**
     * @use HasFactory<Factory>
     */
    use HasUuids, HasFactory;

    /**
     * @var string
     */
    protected string $primaryKey = 'uuid';
}
