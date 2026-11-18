<?php

namespace App\Services;

use App\Exceptions\PhoneNumberAlreadyAssignedException;
use App\Repositories\PhoneNumberRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of PhoneNumberService
 */
readonly class PhoneNumberService implements PhoneNumberServiceInterface
{
    /**
     * @param PhoneNumberRepositoryInterface $phoneNumberRepository
     */
    public function __construct(
        private PhoneNumberRepositoryInterface $phoneNumberRepository,
    ) {}

    /**
     * @param Model $model
     * @param array $phones
     * @return void
     *
     * @throws PhoneNumberAlreadyAssignedException
     */
    public function assignPhone(Model $model, array $phones): void
    {
        foreach ($phones as $phone) {
            if (blank($phone['number'] ?? null)) {
                $this->phoneNumberRepository->removeForOwner($model, $phone['type']);

                continue;
            }

            if ($this->phoneNumberRepository->isOwnedByAnother($phone['number'], $model)) {
                throw new PhoneNumberAlreadyAssignedException;
            }

            $this->phoneNumberRepository->assign($model, $phone['type'], $phone['number']);
        }
    }
}
