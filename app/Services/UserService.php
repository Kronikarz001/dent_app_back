<?php

namespace App\Services;

use App\Enums\PhoneNumberType;
use App\Exceptions\DuplicateUserDataException;
use App\Exceptions\PermissionDeniedException;
use App\Exports\UserExport;
use App\Http\Requests\ExportRequest;
use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Summary of UserService
 */
readonly class UserService implements UserServiceInterface
{
    private const PRIVILEGED_ATTRIBUTES = ['is_admin', 'is_superuser'];

    /**
     * @param UserRepositoryInterface $userRepository
     * @param ExportServiceInterface $exportService
     * @param PhoneNumberServiceInterface $phoneNumberService
     */
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private ExportServiceInterface $exportService,
        private PhoneNumberServiceInterface $phoneNumberService,
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    public function getUsers(): LengthAwarePaginator
    {
        return $this->userRepository->findAllWithPagination();
    }

    /**
     * @return Collection
     */
    public function getUsersList(): Collection
    {
        return $this->userRepository->findSelectAll(['uuid', 'first_name', 'last_name', 'job_position_uuid']);
    }

    /**
     * @return Collection
     */
    public function getUsersWorkingToday(): Collection
    {
        return $this->userRepository->findSelectAll(
            ['uuid', 'first_name', 'last_name', 'job_position_uuid'],
            ['working_on' => Carbon::today()->toDateString()]
        );
    }

    /**
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User
    {
        $this->assertCanChangePrivilegedAttributes($data);
        $data['password'] = Str::password();

        return $this->userRepository->create($data);
    }

    /**
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateUser(User $user, array $data): User
    {
        $this->assertCanChangePrivilegedAttributes($data, $user);
        $this->assertUniqueEmailPrivateEmailAndPesel($data, $user);

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
        return $this->exportService->export($request, new UserExport($this->userRepository->findAllWithPagination(perPage: -1)->getCollection()), User::getModel());
    }

    /**
     * @param array $data
     * @param User|null $user
     * @return void
     *
     * @throws PermissionDeniedException
     */
    private function assertCanChangePrivilegedAttributes(array $data, ?User $user = null): void
    {
        if (Auth::user()?->is_admin) {
            return;
        }

        foreach (self::PRIVILEGED_ATTRIBUTES as $attribute) {
            if (array_key_exists($attribute, $data) && (bool) $data[$attribute] !== (bool) $user?->{$attribute}) {
                throw new PermissionDeniedException('Tylko administrator może nadawać lub odbierać uprawnienia administratora.');
            }
        }
    }

    /**
     * @param array $data
     * @param User $ignore
     * @return void
     *
     * @throws DuplicateUserDataException
     */
    private function assertUniqueEmailPrivateEmailAndPesel(array $data, User $ignore): void
    {
        if (! empty($data['email']) && $this->userRepository->existsBy('email', $data['email'], $ignore->uuid)) {
            throw new DuplicateUserDataException('Użytkownik o podanym adresie e-mail już istnieje.');
        }

        if (! empty($data['private_email']) && $this->userRepository->existsBy('private_email', $data['private_email'], $ignore->uuid)) {
            throw new DuplicateUserDataException('Użytkownik o podanym prywatnym adresie e-mail już istnieje.');
        }

        if (! empty($data['pesel']) && $this->userRepository->existsBy('pesel', $data['pesel'], $ignore->uuid)) {
            throw new DuplicateUserDataException('Użytkownik o podanym numerze PESEL już istnieje.');
        }
    }
}
