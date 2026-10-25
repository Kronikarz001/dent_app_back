<?php

namespace App\Http\Controllers;

use App\Http\Requests\PrescriptionStoreRequest;
use App\Http\Requests\PrescriptionUpdateRequest;
use App\Http\Resources\PrescriptionResource;
use App\Models\Patient;
use App\Models\Prescription;
use App\Services\PrescriptionServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;

/**
 * Summary of PrescriptionController
 */
class PrescriptionController extends Controller
{
    /**
     * @param PrescriptionServiceInterface $prescriptionService
     */
    public function __construct(
        private readonly PrescriptionServiceInterface $prescriptionService
    ) {}

    #[OA\Get(
        path: '/api/prescription',
        summary: 'Lista recept (paginacja)',
        security: [['sanctum' => []]],
        tags: ['Prescription'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/PrescriptionResource')),
                        ]),
                    ]
                )
            ),
        ]
    )]
    public function index(): LengthAwarePaginator
    {
        return $this->prescriptionService->getPrescriptions();
    }

    #[OA\Get(
        path: '/api/patient/{uuid}/prescription',
        summary: 'Lista recept danego pacjenta (paginacja)',
        security: [['sanctum' => []]],
        tags: ['Prescription'],
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
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/PrescriptionResource')),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function byPatient(Patient $patient): AnonymousResourceCollection
    {
        return PrescriptionResource::collection($this->prescriptionService->getPrescriptionsForPatient($patient));
    }

    #[OA\Get(
        path: '/api/prescription/{uuid}',
        summary: 'Pobiera jedną receptę',
        security: [['sanctum' => []]],
        tags: ['Prescription'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/PrescriptionResource')]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function show(Prescription $prescription): PrescriptionResource
    {
        return new PrescriptionResource($this->prescriptionService->getPrescription($prescription));
    }

    #[OA\Post(
        path: '/api/prescription',
        summary: 'Tworzy nową receptę',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(ref: '#/components/requestBodies/PrescriptionRequest'),
        tags: ['Prescription'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Utworzono',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/PrescriptionResource')]
                )
            ),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function store(PrescriptionStoreRequest $request): PrescriptionResource
    {
        $prescription = $this->prescriptionService->createPrescription($request->validated());

        return new PrescriptionResource($this->prescriptionService->getPrescription($prescription));
    }

    #[OA\Put(
        path: '/api/prescription/{uuid}',
        summary: 'Aktualizuje receptę',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(ref: '#/components/requestBodies/PrescriptionRequest'),
        tags: ['Prescription'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Zaktualizowano'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function update(Prescription $prescription, PrescriptionUpdateRequest $request): JsonResponse
    {
        $this->prescriptionService->updatePrescription($prescription, $request->validated());

        return new JsonResponse(null, 204);
    }

    #[OA\Delete(
        path: '/api/prescription/{uuid}',
        summary: 'Usuwa receptę',
        security: [['sanctum' => []]],
        tags: ['Prescription'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Usunięto'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function destroy(Prescription $prescription): JsonResponse
    {
        $this->prescriptionService->deletePrescription($prescription);

        return new JsonResponse(null, 204);
    }

    #[OA\Post(
        path: '/api/prescription/{uuid}/issue',
        summary: 'Wystawia receptę w systemie P1 (eZdrowie) - integracja w przygotowaniu, obecnie zwraca błąd 501',
        security: [['sanctum' => []]],
        tags: ['Prescription'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/PrescriptionResource')]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 501, description: 'Integracja z P1 nie jest jeszcze podłączona'),
        ]
    )]
    public function issue(Prescription $prescription): PrescriptionResource
    {
        $prescription = $this->prescriptionService->issuePrescription($prescription);

        return new PrescriptionResource($this->prescriptionService->getPrescription($prescription));
    }
}
