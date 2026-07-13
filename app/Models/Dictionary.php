<?php

namespace App\Models;

use App\Models\Scopes\DictionaryTypeScope;
use App\Observers\DictionaryObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;

/**
 * @property null|string $key
 * @property null|string $value
 * @property null|object $additional
 * @property string $type
 */
#[ObservedBy(DictionaryObserver::class)]
#[ScopedBy(DictionaryTypeScope::class)]
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
}
