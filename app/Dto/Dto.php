<?php

namespace App\Dto;

use Illuminate\Contracts\Support\Arrayable;
use ReflectionObject;

/**
 * Summary of Dto
 */
abstract class Dto implements Arrayable
{
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

    final public static function fromArray(array $data): static
    {
        return new static(...array_values($data));
    }
}
