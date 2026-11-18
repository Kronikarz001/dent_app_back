<?php

namespace App\Repositories;

use App\Models\PhoneNumber;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of PhoneNumberRepository
 */
class PhoneNumberRepository implements PhoneNumberRepositoryInterface
{
    /**
     * @param Model $owner
     * @param string $type
     * @return PhoneNumber|null
     */
    public function findForOwner(Model $owner, string $type): ?PhoneNumber
    {
        return $this->ownerQuery($owner)->where('type', $type)->first();
    }

    /**
     * @param Model $owner
     * @param string $type
     * @param string $number
     * @return PhoneNumber
     */
    public function assign(Model $owner, string $type, string $number): PhoneNumber
    {
        $existing = $this->findForOwner($owner, $type);

        if ($existing) {
            $existing->update(['number' => $number]);

            return $existing;
        }

        return PhoneNumber::query()->updateOrCreate(
            ['number' => $number],
            [
                'type' => $type,
                'phoneable_type' => $owner::class,
                'phoneable_uuid' => $owner->uuid,
            ]
        );
    }

    /**
     * @param Model $owner
     * @param string $type
     * @return void
     */
    public function removeForOwner(Model $owner, string $type): void
    {
        $this->ownerQuery($owner)->where('type', $type)->delete();
    }

    /**
     * @param string $number
     * @param Model $owner
     * @return bool
     */
    public function isOwnedByAnother(string $number, Model $owner): bool
    {
        return PhoneNumber::query()
            ->where('number', $number)
            ->where(fn (Builder $query) => $query
                ->where('phoneable_type', '!=', $owner::class)
                ->orWhere('phoneable_uuid', '!=', $owner->uuid))
            ->exists();
    }

    /**
     * @param Model $owner
     * @return Builder<PhoneNumber>
     */
    private function ownerQuery(Model $owner): Builder
    {
        return PhoneNumber::query()
            ->where('phoneable_type', $owner::class)
            ->where('phoneable_uuid', $owner->uuid);
    }
}
