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
    public function getUsers(): LengthAwarePaginator;

    public function getUsersList(): LengthAwarePaginator;

    public function createUser(array $data): User;

    public function updateUser(User $user, array $data): User;

    public function deactivateUser(User $user): void;

    public function editPassword(User $user, array $data): User;

    public function getUserInformation(User $user): User;

    public function getUserByToken(string $token): ?User;

    public function getLoggedUser(): User;

    /**
     * @throws Exception
     * @throws WriterException
     */
    public function export(ExportRequest $request): BinaryFileResponse;
}
