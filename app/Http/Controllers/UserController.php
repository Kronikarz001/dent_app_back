<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExportRequest;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Summary of UserController
 */
class UserController extends Controller
{
    public function __construct(
        private readonly UserServiceInterface $userService
    ) {}

    public function index(): LengthAwarePaginator
    {
        return $this->userService->getUsers();
    }

    public function selectList(): LengthAwarePaginator
    {
        return $this->userService->getUsersList();
    }

    public function show(User $user): UserResource
    {
        return new UserResource($user);
    }

    public function showLoggedUser(): UserResource
    {
        return new UserResource($this->userService->getLoggedUser());
    }

    public function store(UserStoreRequest $request): UserResource
    {
        return new UserResource($this->userService->createUser($request->all()));
    }

    public function update(User $user, UserUpdateRequest $request): JsonResponse
    {
        $this->userService->updateUser($user, $request->all());

        return response()->json([], 204);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->userService->deactivateUser($user);

        return response()->json([], 204);
    }

    /**
     * @throws Exception
     * @throws WriterException
     */
    public function export(ExportRequest $request): BinaryFileResponse
    {
        return $this->userService->export($request);
    }
}
