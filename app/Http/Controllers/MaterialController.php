<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExportRequest;
use App\Http\Requests\MaterialRequest;
use App\Http\Resources\MaterialResource;
use App\Models\Material;
use App\Services\MaterialServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Summary of MaterialController
 */
class MaterialController extends Controller
{
    /**
     * @param MaterialServiceInterface $materialService
     */
    public function __construct(
        private readonly MaterialServiceInterface $materialService
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/material',
        summary: 'Lista materiałów (paginacja)',
        security: [['sanctum' => []]],
        tags: ['Material'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/MaterialResource')),
                        ]),
                    ]
                )
            ),
        ]
    )]
    public function index(): LengthAwarePaginator
    {
        return $this->materialService->getMaterials();
    }

    /**
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/material/selectlist',
        summary: 'Materiały do selecta (uuid + name)',
        security: [['sanctum' => []]],
        tags: ['Material'],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
        ]
    )]
    public function selectList(): LengthAwarePaginator
    {
        return $this->materialService->getMaterialsList();
    }

    /**
     * @param Material $material
     * @return MaterialResource
     */
    #[OA\Get(
        path: '/api/material/{uuid}',
        summary: 'Pobiera jeden materiał',
        security: [['sanctum' => []]],
        tags: ['Material'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/MaterialResource')]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function show(Material $material): MaterialResource
    {
        return new MaterialResource($material);
    }

    /**
     * @param MaterialRequest $request
     * @return MaterialResource
     */
    #[OA\Post(
        path: '/api/material',
        summary: 'Tworzy nowy materiał',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Kompozyt światłoutwardzalny'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'short_description', type: 'string', nullable: true),
                    new OA\Property(property: 'price', type: 'integer', example: 50, nullable: true),
                    new OA\Property(property: 'dental_examinations', type: 'array', items: new OA\Items(type: 'string', format: 'uuid'), nullable: true),
                ]
            )
        ),
        tags: ['Material'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Utworzono',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/MaterialResource')]
                )
            ),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function store(MaterialRequest $request): MaterialResource
    {
        return new MaterialResource($this->materialService->createMaterial($request->all()));
    }

    /**
     * @param Material $material
     * @param MaterialRequest $request
     * @return JsonResponse
     */
    #[OA\Put(
        path: '/api/material/{uuid}',
        summary: 'Aktualizuje materiał',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'short_description', type: 'string', nullable: true),
                    new OA\Property(property: 'price', type: 'integer', nullable: true),
                    new OA\Property(property: 'dental_examinations', type: 'array', nullable: true, items: new OA\Items(type: 'string', format: 'uuid')),
                ]
            )
        ),
        tags: ['Material'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Zaktualizowano'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function update(Material $material, MaterialRequest $request): JsonResponse
    {
        $this->materialService->updateMaterial($material, $request->all());

        return new JsonResponse(null, 204);
    }

    /**
     * @param Material $material
     * @return JsonResponse
     */
    #[OA\Delete(
        path: '/api/material/{uuid}',
        summary: 'Usuwa materiał',
        security: [['sanctum' => []]],
        tags: ['Material'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Usunięto'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function destroy(Material $material): JsonResponse
    {
        $this->materialService->deleteMaterial($material);

        return new JsonResponse(null, 204);
    }

    /**
     * @param ExportRequest $request
     * @return BinaryFileResponse
     *
     * @throws Exception
     * @throws WriterException
     */
    #[OA\Get(
        path: '/api/material/export',
        summary: 'Eksport materiałów do pliku',
        security: [['sanctum' => []]],
        tags: ['Material'],
        parameters: [
            new OA\QueryParameter(name: 'type', required: true, schema: new OA\Schema(type: 'string', enum: ['xlsx', 'csv', 'ods'])),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Plik do pobrania', content: new OA\MediaType(mediaType: 'application/octet-stream')),
        ]
    )]
    public function export(ExportRequest $request): BinaryFileResponse
    {
        return $this->materialService->export($request);
    }
}
