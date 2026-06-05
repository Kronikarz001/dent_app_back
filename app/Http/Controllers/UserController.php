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
    /**
     * @param UserServiceInterface $userService
     */
    public function __construct(
        private readonly UserServiceInterface $userService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/user",
     *     tags={"User"},
     *     summary="Lista użytkowników (paginacja)",
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="OK",
     *         @OA\JsonContent(allOf={
     *             @OA\Schema(ref="#/components/schemas/PaginatedResponse"),
     *             @OA\Schema(@OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/UserResource")
     *             ))
     *         })
     *     )
     * )
     *
     * @return LengthAwarePaginator
     */
    public function index(): LengthAwarePaginator
    {
        return $this->userService->getUsers();
    }

    /**
     * @OA\Get(
     *     path="/api/user/selectlist",
     *     tags={"User"},
     *     summary="Lista użytkowników do selecta (uuid + name)",
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="OK")
     * )
     *
     * @return LengthAwarePaginator
     */
    public function selectList(): LengthAwarePaginator
    {
        return $this->userService->getUsersList();
    }

    /**
     * @OA\Get(
     *     path="/api/user/{uuid}",
     *     tags={"User"},
     *     summary="Pobiera jednego użytkownika",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="OK",
     *         @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/UserResource"))
     *     ),
     *     @OA\Response(response=404, description="Nie znaleziono")
     * )
     *
     * @param User $user
     * @return UserResource
     */
    public function show(User $user): UserResource
    {
        return new UserResource($user);
    }

    /**
     * @OA\Get(
     *     path="/api/user/user-info",
     *     tags={"User"},
     *     summary="Dane zalogowanego użytkownika",
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="OK",
     *         @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/UserResource"))
     *     )
     * )
     *
     * @return UserResource
     */
    public function showLoggedUser(): UserResource
    {
        return new UserResource($this->userService->getLoggedUser());
    }

    /**
     * @OA\Post(
     *     path="/api/user",
     *     tags={"User"},
     *     summary="Tworzy nowego użytkownika",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name","last_name","email","pesel","password","password_confirmation"},
     *             @OA\Property(property="first_name", type="string", example="Jan"),
     *             @OA\Property(property="last_name", type="string", example="Kowalski"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="pesel", type="string", example="12345678901"),
     *             @OA\Property(property="password", type="string", format="password"),
     *             @OA\Property(property="password_confirmation", type="string", format="password")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Utworzono",
     *         @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/UserResource"))
     *     ),
     *     @OA\Response(response=422, description="Błąd walidacji")
     * )
     *
     * @param UserStoreRequest $request
     * @return UserResource
     */
    public function store(UserStoreRequest $request): UserResource
    {
        return new UserResource($this->userService->createUser($request->all()));
    }

    /**
     * @OA\Put(
     *     path="/api/user/{uuid}",
     *     tags={"User"},
     *     summary="Aktualizuje użytkownika",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name","last_name","email","pesel"},
     *             @OA\Property(property="first_name", type="string"),
     *             @OA\Property(property="last_name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="pesel", type="string")
     *         )
     *     ),
     *     @OA\Response(response=204, description="Zaktualizowano"),
     *     @OA\Response(response=404, description="Nie znaleziono"),
     *     @OA\Response(response=422, description="Błąd walidacji")
     * )
     *
     * @param User $user
     * @param UserUpdateRequest $request
     * @return JsonResponse
     */
    public function update(User $user, UserUpdateRequest $request): JsonResponse
    {
        $this->userService->updateUser($user, $request->all());

        return new JsonResponse(null, 204);
    }

    /**
     * @OA\Delete(
     *     path="/api/user/{uuid}",
     *     tags={"User"},
     *     summary="Deaktywuje użytkownika",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=204, description="Deaktywowano"),
     *     @OA\Response(response=404, description="Nie znaleziono")
     * )
     *
     * @param User $user
     * @return JsonResponse
     */
    public function destroy(User $user): JsonResponse
    {
        $this->userService->deactivateUser($user);

        return new JsonResponse(null, 204);
    }

    /**
     * @OA\Get(
     *     path="/api/user/export",
     *     tags={"User"},
     *     summary="Eksport użytkowników do pliku",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="type", in="query", required=true,
     *         @OA\Schema(type="string", enum={"xlsx","csv","ods"})
     *     ),
     *     @OA\Response(response=200, description="Plik do pobrania",
     *         @OA\MediaType(mediaType="application/octet-stream")
     *     )
     * )
     *
     * @param ExportRequest $request
     * @return BinaryFileResponse
     *
     * @throws Exception
     * @throws WriterException
     */
    public function export(ExportRequest $request): BinaryFileResponse
    {
        return $this->userService->export($request);
    }
}
