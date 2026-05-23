<?php

namespace App\Dto;

use Illuminate\Contracts\Support\Arrayable;
use ReflectionObject;

/**
 * Summary of Dto
 */
abstract class Dto implements Arrayable
{
    /**
     * @return array
     */
    final public function toArray(): array
    {
        $array = [];
        $ref = new ReflectionObject($this);

        foreach ($ref->getProperties() as $property) {
            $key = $property->getName();
            $snakeKey = strtolower(preg_replace('/[A-Z]/', '_$0', $key));
            $array[$snakeKey] = $this->normalizeValue($property->getValue($this));
        }

        return $array;
    }

    /**
     * @param  mixed  $value
     * @return mixed
     */
    protected function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof Arrayable) {
            return $value->toArray();
        }

        if (is_array($value)) {
            return array_map(fn ($item) => $this->normalizeValue($item), $value);
        }

        return $value;
    }

    /**
     * @param  array  $data
     * @return static
     */
    final public static function fromArray(array $data): static
    {
        return new static(...array_values($data));
    }
}
