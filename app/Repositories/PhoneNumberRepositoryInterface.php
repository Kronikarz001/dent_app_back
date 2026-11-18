<?php

namespace App\Repositories;

use App\Models\PhoneNumber;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of PhoneNumberRepositoryInterface
 */
interface PhoneNumberRepositoryInterface
{
    /**
     * @param Model $owner
     * @param string $type
     * @return PhoneNumber|null
     */
    public function findForOwner(Model $owner, string $type): ?PhoneNumber;

    /**
     * @param Model $owner
     * @param string $type
     * @param string $number
     * @return PhoneNumber
     */
    public function assign(Model $owner, string $type, string $number): PhoneNumber;

    /**
     * @param Model $owner
     * @param string $type
     * @return void
     */
    public function removeForOwner(Model $owner, string $type): void;

    /**
     * @param string $number
     * @param Model $owner
     * @return bool
     */
    public function isOwnedByAnother(string $number, Model $owner): bool;
}
