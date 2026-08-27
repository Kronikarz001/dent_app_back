<?php

namespace App\Services;

use App\Enums\PhoneNumberType;
use App\Exceptions\PermissionDeniedException;
use App\Exports\UserExport;
use App\Http\Requests\ExportRequest;
use App\Models\Permission;
use App\Models\PermissionAssignment;
use App\Models\PermissionGroup;
use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
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
    /**
     * @param UserRepositoryInterface $userRepository
     * @param ExportServiceInterface $exportService
     * @param PhoneNumberServiceInterface $phoneNumberService
     * @param JobPositionServiceInterface $jobPositionService
     * @param PermissionServiceInterface $permissionService
     */
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private ExportServiceInterface $exportService,
        private PhoneNumberServiceInterface $phoneNumberService,
        private JobPositionServiceInterface $jobPositionService,
        private PermissionServiceInterface $permissionService,
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

    /**
     * @param User $user
     * @param array $data
     * @return void
     *
     * @throws PermissionDeniedException
     */
    public function assignPermissions(User $user, array $data): void
    {
        /** @var User $actingUser */
        $actingUser = Auth::user();

        foreach ($data['permissions'] ?? [] as $permissionUuid) {
            $permission = Permission::where('uuid', $permissionUuid)->firstOrFail();

            if (! $actingUser->is_admin && ! $this->permissionService->hasPermissionGrant($actingUser, $permission)) {
                throw new PermissionDeniedException('Nie posiadasz tego uprawnienia, nie możesz go nadać.');
            }

            $this->grant(Permission::class, $permission->uuid, $user, $data['expires_at'] ?? null);
        }

        foreach ($data['permission_groups'] ?? [] as $permissionGroupUuid) {
            $group = PermissionGroup::where('uuid', $permissionGroupUuid)->firstOrFail();

            if (! $actingUser->is_admin && ! $this->permissionService->hasGroupGrant($actingUser, $group)) {
                throw new PermissionDeniedException('Nie posiadasz tej grupy uprawnień, nie możesz jej nadać.');
            }

            $this->grant(PermissionGroup::class, $group->uuid, $user, $data['expires_at'] ?? null);
        }
    }

    /**
     * @param string $grantableType
     * @param string $grantableUuid
     * @param User $user
     * @param string|null $expiresAt
     * @return void
     */
    private function grant(string $grantableType, string $grantableUuid, User $user, ?string $expiresAt): void
    {
        PermissionAssignment::firstOrCreate([
            'grantable_type' => $grantableType,
            'grantable_id' => $grantableUuid,
            'assignable_type' => User::class,
            'assignable_id' => $user->uuid,
        ], [
            'expires_at' => $expiresAt,
            'granted_by' => Auth::id(),
        ]);
    }
}
