<?php

namespace App\Http\Controllers;

use App\Dto\FileDto;
use App\Enums\FileableType;
use App\Http\Requests\FileStoreRequest;
use App\Http\Requests\FileUpdateRequest;
use App\Http\Resources\FileResource;
use App\Models\File;
use App\Models\User;
use App\Models\UserAvatar;
use App\Models\UserBackground;
use App\Services\FileServiceInterface;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of UserFileController
 */
class UserFileController extends Controller
{
    /**
     * @param FileServiceInterface $fileService
     */
    public function __construct(
        private readonly FileServiceInterface $fileService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/user/{user}/file",
     *     tags={"UserFile"},
     *     summary="Lista plików użytkownika (paginacja)",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\Response(response=200, description="OK",
     *
     *         @OA\JsonContent(allOf={
     *
     *             @OA\Schema(ref="#/components/schemas/PaginatedResponse"),
     *             @OA\Schema(@OA\Property(property="data", type="array",
     *
     *                 @OA\Items(ref="#/components/schemas/FileResource")
     *             ))
     *         })
     *     )
     * )
     *
     * @param User $user
     * @return LengthAwarePaginator
     */
    public function index(User $user): LengthAwarePaginator
    {
        return $this->fileService->getAllFiles($user);
    }

    /**
     * @OA\Post(
     *     path="/api/user/{user}/file",
     *     tags={"UserFile"},
     *     summary="Wgrywa pliki dla użytkownika",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *                 required={"files[]"},
     *
     *                 @OA\Property(property="files[]", type="array",
     *
     *                     @OA\Items(type="string", format="binary")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="OK",
     *
     *         @OA\JsonContent(@OA\Property(property="data", type="array",
     *
     *             @OA\Items(ref="#/components/schemas/FileResource")
     *         ))
     *     )
     * )
     *
     * @param User $user
     * @param FileStoreRequest $request
     * @return AnonymousResourceCollection
     */
    public function store(User $user, FileStoreRequest $request): AnonymousResourceCollection
    {
        return FileResource::collection(
            $this->fileService->saveFile(
                new FileDto($request->file('files'), FileableType::USER),
                $user
            )
        );
    }

    /**
     * @OA\Get(
     *     path="/api/user/{user}/file/{file}",
     *     tags={"UserFile"},
     *     summary="Pobiera metadane pliku użytkownika",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="file", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\Response(response=200, description="OK",
     *
     *         @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/FileResource"))
     *     ),
     *
     *     @OA\Response(response=404, description="Nie znaleziono")
     * )
     *
     * @param User $user
     * @param File $file
     * @return FileResource
     */
    public function show(User $user, File $file): FileResource
    {
        return new FileResource($file);
    }

    /**
     * @OA\Get(
     *     path="/api/user/{user}/file-download/{file}",
     *     tags={"UserFile"},
     *     summary="Pobiera zawartość pliku użytkownika",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="file", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=404, description="Nie znaleziono")
     * )
     *
     * @param User $user
     * @param File $file
     * @return JsonResponse
     *
     * @throws FileNotFoundException
     */
    public function download(User $user, File $file): JsonResponse
    {
        return new JsonResponse($this->fileService->getFile($file));
    }

    /**
     * @OA\Put(
     *     path="/api/user/{user}/file/{file}",
     *     tags={"UserFile"},
     *     summary="Zmienia nazwę pliku użytkownika",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="file", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"filename"},
     *
     *             @OA\Property(property="filename", type="string", example="nowa_nazwa")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="OK",
     *
     *         @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/FileResource"))
     *     ),
     *
     *     @OA\Response(response=404, description="Nie znaleziono"),
     *     @OA\Response(response=422, description="Błąd walidacji")
     * )
     *
     * @param User $user
     * @param File $file
     * @param FileUpdateRequest $request
     * @return FileResource
     */
    public function update(User $user, File $file, FileUpdateRequest $request): FileResource
    {
        return new FileResource(
            $this->fileService->updateFileName($file, $request->input('filename'))
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/user/{user}/file/{file}",
     *     tags={"UserFile"},
     *     summary="Usuwa plik użytkownika",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="file", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\Response(response=204, description="Usunięto"),
     *     @OA\Response(response=404, description="Nie znaleziono")
     * )
     *
     * @param User $user
     * @param File $file
     * @return JsonResponse
     */
    public function destroy(User $user, File $file): JsonResponse
    {
        $this->fileService->deleteFile($file);

        return new JsonResponse(null, 204);
    }

    /**
     * @OA\Post(
     *     path="/api/user/{user}/file-new-version/{file}",
     *     tags={"UserFile"},
     *     summary="Tworzy nową wersję pliku użytkownika",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="file", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *                 required={"files[]"},
     *
     *                 @OA\Property(property="files[]", type="array",
     *
     *                     @OA\Items(type="string", format="binary")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="OK",
     *
     *         @OA\JsonContent(@OA\Property(property="data", type="array",
     *
     *             @OA\Items(ref="#/components/schemas/FileResource")
     *         ))
     *     )
     * )
     *
     * @param User $user
     * @param File $file
     * @param FileStoreRequest $request
     * @return AnonymousResourceCollection
     */
    public function storeNewVersion(User $user, File $file, FileStoreRequest $request): AnonymousResourceCollection
    {
        return FileResource::collection(
            $this->fileService->createNewVersionFile(
                $file,
                new FileDto($request->file('files'), FileableType::USER),
                $user
            )
        );
    }

    /**
     * @OA\Post(
     *     path="/api/user/{user}/avatar",
     *     tags={"UserFile"},
     *     summary="Wgrywa avatar użytkownika",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *                 required={"files[]"},
     *
     *                 @OA\Property(property="files[]", type="array",
     *
     *                     @OA\Items(type="string", format="binary")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="OK",
     *
     *         @OA\JsonContent(@OA\Property(property="data", type="array",
     *
     *             @OA\Items(ref="#/components/schemas/FileResource")
     *         ))
     *     )
     * )
     *
     * @param User $user
     * @param FileStoreRequest $request
     * @return AnonymousResourceCollection
     */
    public function storeAvatar(User $user, FileStoreRequest $request): AnonymousResourceCollection
    {
        return FileResource::collection(
            $this->fileService->saveFile(
                new FileDto($request->file('files'), FileableType::USER_AVATAR),
                UserAvatar::find($user->uuid)
            )
        );
    }

    /**
     * @OA\Get(
     *     path="/api/user/{user}/file-avatar-download/{file}",
     *     tags={"UserFile"},
     *     summary="Pobiera avatar użytkownika (obraz binarny)",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="file", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\Response(response=200, description="Obraz",
     *
     *         @OA\MediaType(mediaType="image/*")
     *     ),
     *
     *     @OA\Response(response=404, description="Nie znaleziono")
     * )
     *
     * @param User $user
     * @param File $file
     * @return Response
     *
     * @throws FileNotFoundException
     */
    public function avatarDownload(User $user, File $file): Response
    {
        return response($this->fileService->getPhotoFile($file), 200, ['Content-Type' => $file->mimetype]);
    }

    /**
     * @OA\Post(
     *     path="/api/user/{user}/background",
     *     tags={"UserFile"},
     *     summary="Wgrywa tło użytkownika",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *                 required={"files[]"},
     *
     *                 @OA\Property(property="files[]", type="array",
     *
     *                     @OA\Items(type="string", format="binary")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="OK",
     *
     *         @OA\JsonContent(@OA\Property(property="data", type="array",
     *
     *             @OA\Items(ref="#/components/schemas/FileResource")
     *         ))
     *     )
     * )
     *
     * @param User $user
     * @param FileStoreRequest $request
     * @return AnonymousResourceCollection
     */
    public function storeBackground(User $user, FileStoreRequest $request): AnonymousResourceCollection
    {
        return FileResource::collection(
            $this->fileService->saveFile(
                new FileDto($request->file('files'), FileableType::USER_BACKGROUND),
                UserBackground::find($user->uuid)
            )
        );
    }

    /**
     * @OA\Get(
     *     path="/api/user/{user}/file-background-download/{file}",
     *     tags={"UserFile"},
     *     summary="Pobiera tło użytkownika (obraz binarny)",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="file", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\Response(response=200, description="Obraz",
     *
     *         @OA\MediaType(mediaType="image/*")
     *     ),
     *
     *     @OA\Response(response=404, description="Nie znaleziono")
     * )
     *
     * @param User $user
     * @param File $file
     * @return Response
     *
     * @throws FileNotFoundException
     */
    public function backgroundDownload(User $user, File $file): Response
    {
        return response($this->fileService->getPhotoFile($file), 200, ['Content-Type' => $file->mimetype]);
    }
}
