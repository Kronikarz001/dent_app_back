<?php

namespace App\Services;

use App\Http\Requests\ExportRequest;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Summary of UserServiceInterface
 */
interface UserServiceInterface
{
    /**
     * @return LengthAwarePaginator
     */
    public function getUsers(): LengthAwarePaginator;

    /**
     * @return LengthAwarePaginator
     */
    public function getUsersList(): LengthAwarePaginator;

    /**
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User;

    /**
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateUser(User $user, array $data): User;

    /**
     * @param User $user
     * @return void
     */
    public function deactivateUser(User $user): void;

    /**
     * @param User $user
     * @param array $data
     * @return User
     */
    public function editPassword(User $user, array $data): User;

    /**
     * @return User
     */
    public function getLoggedUser(): User;

    /**
     * @param ExportRequest $request
     * @return BinaryFileResponse
     *
     * @throws Exception
     * @throws WriterException
     */
    public function export(ExportRequest $request): BinaryFileResponse;

    /**
     * @param User $user
     * @param array $data
     * @return void
     */
    public function assignPermissions(User $user, array $data): void;
}
