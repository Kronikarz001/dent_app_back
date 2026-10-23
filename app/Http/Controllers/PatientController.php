<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExportRequest;
use App\Http\Requests\PatientStoreRequest;
use App\Http\Requests\PatientUpdateRequest;
use App\Http\Resources\PatientResource;
use App\Http\Resources\PatientRomexisSyncResource;
use App\Http\Resources\PatientStatisticsResource;
use App\Http\Resources\PatientVisitResource;
use App\Models\Patient;
use App\Services\PatientServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PatientController extends Controller
{
    public function __construct(
        private readonly PatientServiceInterface $patientService
    ) {}

    #[OA\Get(
        path: '/api/patient',
        summary: 'Lista pacjentów (paginacja)',
        security: [['sanctum' => []]],
        tags: ['Patient'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/PatientResource')),
                        ]),
                    ]
                )
            ),
        ]
    )]
    public function index(): LengthAwarePaginator
    {
        return $this->patientService->getPatients();
    }

    #[OA\Get(
        path: '/api/patient/selectlist',
        summary: 'Lista pacjentów do selecta (uuid + name)',
        security: [['sanctum' => []]],
        tags: ['Patient'],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
        ]
    )]
    public function selectList(): LengthAwarePaginator
    {
        return $this->patientService->getPatientsList();
    }

    #[OA\Get(
        path: '/api/patient/romexis-sync-list',
        summary: 'Lista pacjentów z ustawionym romexis_patient_id (do synchronizacji zdjęć przez agenta Romexis)',
        security: [['sanctum' => []]],
        tags: ['Patient'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/PatientRomexisSyncResource')),
                        ]),
                    ]
                )
            ),
        ]
    )]
    public function romexisSyncList(): AnonymousResourceCollection
    {
        return PatientRomexisSyncResource::collection($this->patientService->getPatientsWithRomexisId());
    }

    #[OA\Get(
        path: '/api/patient/{uuid}',
        summary: 'Pobiera jednego pacjenta',
        security: [['sanctum' => []]],
        tags: ['Patient'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/PatientResource')]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function show(Patient $patient): PatientResource
    {
        return new PatientResource($patient);
    }

    #[OA\Get(
        path: '/api/patient/{uuid}/statistics',
        summary: 'Statystyki pacjenta (ostatnia/następna wizyta, nieobecności, lekarz prowadzący)',
        security: [['sanctum' => []]],
        tags: ['Patient'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(ref: '#/components/schemas/PatientStatisticsResource')
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function statistics(Patient $patient): PatientStatisticsResource
    {
        return new PatientStatisticsResource($this->patientService->getStatistics($patient));
    }

    #[OA\Get(
        path: '/api/patient/{uuid}/visits',
        summary: 'Archiwum wizyt pacjenta (paginacja, od najnowszej)',
        security: [['sanctum' => []]],
        tags: ['Patient'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/PatientVisitResource')),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function visits(Patient $patient): AnonymousResourceCollection
    {
        return PatientVisitResource::collection($this->patientService->getVisits($patient));
    }

    #[OA\Post(
        path: '/api/patient',
        summary: 'Tworzy nowego pacjenta',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['first_name', 'last_name', 'email', 'pesel'],
                properties: [
                    new OA\Property(property: 'first_name', type: 'string', example: 'Anna'),
                    new OA\Property(property: 'last_name', type: 'string', example: 'Nowak'),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'pesel', type: 'string', example: '98010112345'),
                    new OA\Property(property: 'street', type: 'string', nullable: true),
                    new OA\Property(property: 'house_number', type: 'string', nullable: true),
                    new OA\Property(property: 'apartment_number', type: 'string', nullable: true),
                    new OA\Property(property: 'postal_code', type: 'string', nullable: true),
                    new OA\Property(property: 'city', type: 'string', nullable: true),
                    new OA\Property(property: 'gender', type: 'string', enum: ['MALE', 'FEMALE'], nullable: true),
                    new OA\Property(property: 'notes', type: 'string', nullable: true),
                ]
            )
        ),
        tags: ['Patient'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Utworzono',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/PatientResource')]
                )
            ),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function store(PatientStoreRequest $request): PatientResource
    {
        return new PatientResource($this->patientService->createPatient($request->all()));
    }

    #[OA\Put(
        path: '/api/patient/{uuid}',
        summary: 'Aktualizuje pacjenta',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['first_name', 'last_name', 'email'],
                properties: [
                    new OA\Property(property: 'first_name', type: 'string'),
                    new OA\Property(property: 'last_name', type: 'string'),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(
                        property: 'phone_numbers',
                        type: 'array',
                        items: new OA\Items(properties: [
                            new OA\Property(property: 'number', type: 'string', example: '48500100200'),
                            new OA\Property(property: 'type', type: 'string', enum: ['PRIVATE', 'WORK']),
                        ]),
                        nullable: true
                    ),
                    new OA\Property(property: 'street', type: 'string', nullable: true),
                    new OA\Property(property: 'house_number', type: 'string', nullable: true),
                    new OA\Property(property: 'apartment_number', type: 'string', nullable: true),
                    new OA\Property(property: 'postal_code', type: 'string', nullable: true),
                    new OA\Property(property: 'city', type: 'string', nullable: true),
                    new OA\Property(property: 'gender', type: 'string', enum: ['MALE', 'FEMALE'], nullable: true),
                    new OA\Property(property: 'notes', type: 'string', nullable: true),
                    new OA\Property(property: 'doctor_uuid', type: 'string', format: 'uuid', nullable: true),
                ]
            )
        ),
        tags: ['Patient'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Zaktualizowano'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function update(Patient $patient, PatientUpdateRequest $request): JsonResponse
    {
        $this->patientService->updatePatient($patient, $request->all());

        return new JsonResponse(null, 204);
    }

    #[OA\Delete(
        path: '/api/patient/{uuid}',
        summary: 'Usuwa pacjenta',
        security: [['sanctum' => []]],
        tags: ['Patient'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Usunięto'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function destroy(Patient $patient): JsonResponse
    {
        $this->patientService->deletePatient($patient);

        return new JsonResponse(null, 204);
    }

    #[OA\Get(
        path: '/api/patient/export',
        summary: 'Eksport pacjentów do pliku',
        security: [['sanctum' => []]],
        tags: ['Patient'],
        parameters: [
            new OA\QueryParameter(name: 'type', required: true, schema: new OA\Schema(type: 'string', enum: ['xlsx', 'csv', 'ods'])),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Plik do pobrania', content: new OA\MediaType(mediaType: 'application/octet-stream')),
        ]
    )]
    public function export(ExportRequest $request): BinaryFileResponse
    {
        return $this->patientService->export($request);
    }
}
