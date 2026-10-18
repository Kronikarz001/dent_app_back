<?php

namespace App\Models;

use App\Observers\DictionaryObserver;
use App\Scopes\DictionaryTypeScope;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

/**
 * @property null|string $key
 * @property null|string $value
 * @property null|object $additional
 * @property string $type
 */
#[ObservedBy(DictionaryObserver::class)]
abstract class Dictionary extends UuidModel
{
    protected $table = 'dictionaries';

    /**
     * @var string[]
     */
    protected $fillable = [
        'key',
        'value',
        'additional',
    ];

    /**
     * @return void
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new DictionaryTypeScope);
    }
}
