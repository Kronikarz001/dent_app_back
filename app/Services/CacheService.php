<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Summary of CacheService
 */
abstract class CacheService implements CacheServiceInterface
{

    /**
     * @param $key
     * @return object
     */
    public function get($key): mixed
    {
        return Cache::get($this->getPrefix($key));
    }

    /**
     * @param string $key
     * @param object $data
     * @return void
     */
    public function set(mixed $key, mixed $data): void
    {
        Cache::put($this->getPrefix($key), $data, $this->getTtl());
    }

    /**
     * @param string $key
     * @return void
     */
    public function delete(mixed $key): void
    {
        Cache::delete($this->getPrefix($key));
    }

    /**
     * @param mixed $key
     * @return bool
     */
    public function has(mixed $key): bool
    {
        return Cache::has($this->getPrefix($key));
    }

    /**
     * @return string
     */
    protected abstract function getModulePrefix(): string;

    /**
     * @return int|null
     */
    protected abstract function getTtl(): ?int;

    /**
     * @param string $key
     * @return string
     */
    public function getPrefix(string $key): string
    {
        return "{$this->getModulePrefix()}_$key";
    }
}
