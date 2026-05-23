<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Summary of CacheService
 */
abstract class CacheService implements CacheServiceInterface
{
    /**
     * @return object
     */
    public function get($key): mixed
    {
        return Cache::get($this->getPrefix($key));
    }

    /**
     * @param  string  $key
     * @param  object  $data
     */
    public function set(mixed $key, mixed $data): void
    {
        Cache::put($this->getPrefix($key), $data, $this->getTtl());
    }

    /**
     * @param  string  $key
     */
    public function delete(mixed $key): void
    {
        Cache::delete($this->getPrefix($key));
    }

    public function has(mixed $key): bool
    {
        return Cache::has($this->getPrefix($key));
    }

    abstract protected function getModulePrefix(): string;

    abstract protected function getTtl(): ?int;

    public function getPrefix(string $key): string
    {
        return "{$this->getModulePrefix()}_$key";
    }
}
