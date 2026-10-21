<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatientPhotoStoreRequest;
use App\Http\Requests\PatientPhotoUpdateRequest;
use App\Http\Resources\PhotoResource;
use App\Models\Patient;
use App\Models\Photo;
use App\Services\PhotoServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;

class PatientPhotoController extends Controller
{
    public function __construct(
        private readonly PhotoServiceInterface $photoService
    ) {}

    #[OA\Get(
        path: '/api/patient/{patient}/photo',
        tags: ['PatientPhoto'],
        summary: 'Lista zdjęć pacjenta (paginacja)',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'patient', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/PhotoResource')),
                        ]),
                    ]
                )
            ),
        ]
    )]
    public function index(Patient $patient): LengthAwarePaginator
    {
        return $this->photoService->getAllPhotos($patient);
    }

    #[OA\Post(
        path: '/api/patient/{patient}/photo',
        tags: ['PatientPhoto'],
        summary: 'Wgrywa zdjęcia dla pacjenta',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'patient', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(ref: '#/components/requestBodies/FileUploadRequest'),
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/PhotoResource'))]
                )
            ),
        ]
    )]
    public function store(Patient $patient, PatientPhotoStoreRequest $request): AnonymousResourceCollection
    {
        return PhotoResource::collection(
            $this->photoService->savePhotos($request->file('files'), $patient)
        );
    }

    #[OA\Get(
        path: '/api/patient/{patient}/photo/{photo}',
        tags: ['PatientPhoto'],
        summary: 'Pobiera metadane zdjęcia pacjenta',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'patient', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\PathParameter(name: 'photo', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/PhotoResource')]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function show(Patient $patient, Photo $photo): PhotoResource
    {
        $this->assertPhotoBelongsTo($photo, $patient);

        return new PhotoResource($photo);
    }

    #[OA\Get(
        path: '/api/patient/{patient}/photo-download/{photo}',
        tags: ['PatientPhoto'],
        summary: 'Pobiera zawartość zdjęcia pacjenta',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'patient', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\PathParameter(name: 'photo', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function download(Patient $patient, Photo $photo): JsonResponse
    {
        $this->assertPhotoBelongsTo($photo, $patient);

        return new JsonResponse($this->photoService->getPhoto($photo));
    }

    #[OA\Put(
        path: '/api/patient/{patient}/photo/{photo}',
        tags: ['PatientPhoto'],
        summary: 'Zmienia nazwę zdjęcia pacjenta',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'patient', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\PathParameter(name: 'photo', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(ref: '#/components/requestBodies/FileRenameRequest'),
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/PhotoResource')]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function update(Patient $patient, Photo $photo, PatientPhotoUpdateRequest $request): PhotoResource
    {
        $this->assertPhotoBelongsTo($photo, $patient);

        return new PhotoResource(
            $this->photoService->updatePhotoName($photo, $request->input('filename'))
        );
    }

    #[OA\Delete(
        path: '/api/patient/{patient}/photo/{photo}',
        tags: ['PatientPhoto'],
        summary: 'Usuwa zdjęcie pacjenta',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'patient', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\PathParameter(name: 'photo', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Usunięto'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function destroy(Patient $patient, Photo $photo): JsonResponse
    {
        $this->assertPhotoBelongsTo($photo, $patient);

        $this->photoService->deletePhoto($photo);

        return new JsonResponse(null, 204);
    }

    /**
     * @param Photo $photo
     * @param Patient $patient
     * @return void
     *
     * @throws ModelNotFoundException
     */
    private function assertPhotoBelongsTo(Photo $photo, Patient $patient): void
    {
        if ($photo->patient_uuid !== $patient->uuid) {
            throw (new ModelNotFoundException)->setModel(Photo::class, [$photo->uuid]);
        }
    }
}
