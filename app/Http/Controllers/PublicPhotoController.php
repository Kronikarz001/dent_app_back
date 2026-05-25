<?php

namespace App\Http\Controllers;

use App\Dto\FileDto;
use App\Enums\FileableType;
use App\Http\Requests\FileUpdateRequest;
use App\Http\Requests\PublicPhotoStoreRequest;
use App\Http\Resources\FileResource;
use App\Models\File;
use App\Services\FileServiceInterface;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Summary of PublicPhotoController
 */
class PublicPhotoController extends Controller
{
    public function __construct(
        private readonly FileServiceInterface $fileService,
    ) {}

    /**
     * @param File $file
     * @return Response
     *
     * @throws FileNotFoundException
     */
    public function show(File $file): Response
    {
        return response($this->fileService->getFileContent($file), 200, ['Content-Type' => $file->mimetype]);
    }

    /**
     * @param PublicPhotoStoreRequest $request
     * @return JsonResponse
     */
    public function store(PublicPhotoStoreRequest $request): JsonResponse
    {
        $type = FileableType::map($request->input('fileable_type'));
        $model = ($type->value)::findOrFail($request->input('fileable_id'));

        $files = $this->fileService->saveFile(
            new FileDto($request->file('files'), $type),
            $model
        );

        return response()->json(FileResource::collection($files), 201);
    }

    /**
     * @param File $file
     * @param FileUpdateRequest $request
     * @return FileResource
     */
    public function update(File $file, FileUpdateRequest $request): FileResource
    {
        return new FileResource(
            $this->fileService->updateFileName($file, $request->input('filename'))
        );
    }

    /**
     * @param File $file
     * @return JsonResponse
     *
     * @throws FileNotFoundException
     */
    public function destroy(File $file): JsonResponse
    {
        $this->fileService->deleteFile($file);

        return response()->json([], 204);
    }
}
