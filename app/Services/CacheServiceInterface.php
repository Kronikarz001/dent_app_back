<?php

namespace App\Services;

/**
 * @template TKey
 * @template TValue
 */
interface CacheServiceInterface
{
    /**
     * @param  TKey  $key
     * @return TValue
     */
    public function get(mixed $key): mixed;

    /**
     * @param  TKey  $key
     * @param  TValue  $data
     */
    public function set(mixed $key, object $data): void;

    /**
     * @param  TKey  $key
     */
    public function delete(mixed $key): void;

    /**
     * @param  TKey  $key
     */
    public function has(mixed $key): bool;
}
