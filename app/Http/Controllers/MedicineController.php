<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedicineStoreRequest;
use App\Http\Requests\MedicineUpdateRequest;
use App\Http\Resources\MedicineResource;
use App\Models\Medicine;
use App\Services\MedicineServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;

/**
 * Summary of MedicineController
 */
class MedicineController extends Controller
{
    /**
     * @param MedicineServiceInterface $medicineService
     */
    public function __construct(
        private readonly MedicineServiceInterface $medicineService
    ) {}

    #[OA\Get(
        path: '/api/medicine',
        summary: 'Lista leków (paginacja)',
        security: [['sanctum' => []]],
        tags: ['Medicine'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/MedicineResource')),
                        ]),
                    ]
                )
            ),
        ]
    )]
    public function index(): LengthAwarePaginator
    {
        return $this->medicineService->getMedicines();
    }

    #[OA\Get(
        path: '/api/medicine/selectlist',
        summary: 'Leki do selecta (uuid + name)',
        security: [['sanctum' => []]],
        tags: ['Medicine'],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
        ]
    )]
    public function selectList(): LengthAwarePaginator
    {
        return $this->medicineService->getMedicinesList();
    }

    #[OA\Get(
        path: '/api/medicine/{uuid}',
        summary: 'Pobiera jeden lek',
        security: [['sanctum' => []]],
        tags: ['Medicine'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/MedicineResource')]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function show(Medicine $medicine): MedicineResource
    {
        return new MedicineResource($medicine);
    }

    #[OA\Post(
        path: '/api/medicine',
        summary: 'Tworzy nowy lek',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'reimbursement_level'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Apap'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'ean_gtin', type: 'string', nullable: true),
                    new OA\Property(property: 'reimbursement_level', type: 'string', enum: ['FULL', 'FLAT_RATE', 'FREE', 'R', 'OTHER']),
                    new OA\Property(property: 'external_reference', type: 'string', nullable: true),
                ]
            )
        ),
        tags: ['Medicine'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Utworzono',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/MedicineResource')]
                )
            ),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function store(MedicineStoreRequest $request): MedicineResource
    {
        return new MedicineResource($this->medicineService->createMedicine($request->validated()));
    }

    #[OA\Put(
        path: '/api/medicine/{uuid}',
        summary: 'Aktualizuje lek',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'reimbursement_level'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'ean_gtin', type: 'string', nullable: true),
                    new OA\Property(property: 'reimbursement_level', type: 'string', enum: ['FULL', 'FLAT_RATE', 'FREE', 'R', 'OTHER']),
                    new OA\Property(property: 'external_reference', type: 'string', nullable: true),
                ]
            )
        ),
        tags: ['Medicine'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Zaktualizowano'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function update(Medicine $medicine, MedicineUpdateRequest $request): JsonResponse
    {
        $this->medicineService->updateMedicine($medicine, $request->validated());

        return new JsonResponse(null, 204);
    }

    #[OA\Delete(
        path: '/api/medicine/{uuid}',
        summary: 'Usuwa lek',
        security: [['sanctum' => []]],
        tags: ['Medicine'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Usunięto'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function destroy(Medicine $medicine): JsonResponse
    {
        $this->medicineService->deleteMedicine($medicine);

        return new JsonResponse(null, 204);
    }
}
