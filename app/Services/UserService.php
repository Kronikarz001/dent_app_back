<?php

namespace App\Services;

use App\Enums\PhoneNumberType;
use App\Exports\UserExport;
use App\Http\Requests\ExportRequest;
use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Summary of UserService
 */
readonly class UserService implements UserServiceInterface
{
    /**
     * @param UserRepositoryInterface $userRepository
     * @param ExportServiceInterface $exportService
     * @param PhoneNumberServiceInterface $phoneNumberService
     * @param JobPositionServiceInterface $jobPositionService
     */
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private ExportServiceInterface $exportService,
        private PhoneNumberServiceInterface $phoneNumberService,
        private JobPositionServiceInterface $jobPositionService
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    public function getUsers(): LengthAwarePaginator
    {
        return $this->userRepository->findAllWithPagination();
    }

    /**
     * @return LengthAwarePaginator
     */
    public function getUsersList(): LengthAwarePaginator
    {
        return $this->userRepository->findAllWithPagination(['uuid', 'name']);
    }

    /**
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User
    {
        return $this->userRepository->create($data);
    }

    /**
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateUser(User $user, array $data): User
    {
        if (array_key_exists('private_phone_number', $data)) {
            $this->phoneNumberService->assignPhone($user, [
                ['type' => PhoneNumberType::PRIVATE->value, 'number' => $data['private_phone_number']],
            ]);
        }

        if (array_key_exists('phone_number', $data)) {
            $this->phoneNumberService->assignPhone($user, [
                ['type' => PhoneNumberType::WORK->value, 'number' => $data['phone_number']],
            ]);
        }

        if (array_key_exists('job_positions', $data)) {
            $this->jobPositionService->assignJobPosition($user, ['job_positions' => $data['job_positions']]);
        }

        return $this->userRepository->update($user, $data);
    }

    /**
     * @param User $user
     * @return void
     */
    public function deactivateUser(User $user): void
    {
        $this->userRepository->update($user, ['is_active' => false]);
    }

    /**
     * @param User $user
     * @return void
     */
    public function deleteUser(User $user): void
    {
        $this->userRepository->delete($user);
    }

    /**
     * @param User $user
     * @param array $data
     * @return User
     */
    public function editPassword(User $user, array $data): User
    {
        $data['password'] = bcrypt($data['password']);

        return $this->userRepository->update($user, $data);
    }

    /**
     * @param User $user
     * @return User
     */
    public function getUserInformation(User $user): User
    {
        return $this->userRepository->getUserInformation($user->uuid);
    }

    /**
     * @param string $token
     * @return User|null
     */
    public function getUserByToken(string $token): ?User
    {
        return $this->userRepository->getUserByToken($token);
    }

    /**
     * @return User
     */
    public function getLoggedUser(): User
    {
        return $this->userRepository->getLoggedUser();
    }

    /**
     * @param ExportRequest $request
     * @return BinaryFileResponse
     *
     * @throws Exception
     * @throws WriterException
     */
    public function export(ExportRequest $request): BinaryFileResponse
    {
        return $this->exportService->export($request, new UserExport($this->getUsers()->getCollection()), User::getModel());
    }
}
