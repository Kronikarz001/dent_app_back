<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExportRequest;
use App\Http\Requests\PatientStoreRequest;
use App\Http\Requests\PatientUpdateRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use App\Services\PatientServiceInterface;
use Illuminate\Http\JsonResponse;
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
        tags: ['Patient'],
        summary: 'Lista pacjentów (paginacja)',
        security: [['sanctum' => []]],
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
        tags: ['Patient'],
        summary: 'Lista pacjentów do selecta (uuid + name)',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
        ]
    )]
    public function selectList(): LengthAwarePaginator
    {
        return $this->patientService->getPatientsList();
    }

    #[OA\Get(
        path: '/api/patient/{uuid}',
        tags: ['Patient'],
        summary: 'Pobiera jednego pacjenta',
        security: [['sanctum' => []]],
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

    #[OA\Post(
        path: '/api/patient',
        tags: ['Patient'],
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
        tags: ['Patient'],
        summary: 'Aktualizuje pacjenta',
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
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
                        nullable: true,
                        items: new OA\Items(properties: [
                            new OA\Property(property: 'number', type: 'string', example: '48500100200'),
                            new OA\Property(property: 'type', type: 'string', enum: ['PRIVATE', 'WORK']),
                        ])
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
        tags: ['Patient'],
        summary: 'Usuwa pacjenta',
        security: [['sanctum' => []]],
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
        tags: ['Patient'],
        summary: 'Eksport pacjentów do pliku',
        security: [['sanctum' => []]],
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
