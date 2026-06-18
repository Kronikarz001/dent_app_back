<?php

namespace App\Http\Controllers;

use App\Http\Requests\DentalExaminationRequest;
use App\Http\Requests\ExportRequest;
use App\Http\Resources\DentalExaminationResource;
use App\Models\DentalExamination;
use App\Services\DentalExaminationServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Summary of DentalExaminationController
 */
class DentalExaminationController extends Controller
{
    /**
     * @param DentalExaminationServiceInterface $dentalExaminationService
     */
    public function __construct(
        private readonly DentalExaminationServiceInterface $dentalExaminationService
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/dental-examination',
        summary: 'Lista badań stomatologicznych (paginacja)',
        security: [['sanctum' => []]],
        tags: ['DentalExamination'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/DentalExaminationResource')),
                        ]),
                    ]
                )
            ),
        ]
    )]
    public function index(): LengthAwarePaginator
    {
        return $this->dentalExaminationService->getDentalExaminations();
    }

    /**
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/dental-examination/selectlist',
        summary: 'Badania stomatologiczne do selecta (uuid + name)',
        security: [['sanctum' => []]],
        tags: ['DentalExamination'],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
        ]
    )]
    public function selectList(): LengthAwarePaginator
    {
        return $this->dentalExaminationService->getDentalExaminationsList();
    }

    /**
     * @param DentalExamination $dentalExamination
     * @return DentalExaminationResource
     */
    #[OA\Get(
        path: '/api/dental-examination/{uuid}',
        summary: 'Pobiera jedno badanie stomatologiczne',
        security: [['sanctum' => []]],
        tags: ['DentalExamination'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/DentalExaminationResource')]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function show(DentalExamination $dentalExamination): DentalExaminationResource
    {
        return new DentalExaminationResource($dentalExamination);
    }

    /**
     * @param DentalExaminationRequest $request
     * @return DentalExaminationResource
     */
    #[OA\Post(
        path: '/api/dental-examination',
        summary: 'Tworzy nowe badanie stomatologiczne',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Przegląd jamy ustnej'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'short_description', type: 'string', nullable: true),
                    new OA\Property(property: 'price', type: 'integer', nullable: true, example: 150),
                ]
            )
        ),
        tags: ['DentalExamination'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Utworzono',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/DentalExaminationResource')]
                )
            ),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function store(DentalExaminationRequest $request): DentalExaminationResource
    {
        return new DentalExaminationResource($this->dentalExaminationService->createDentalExamination($request->all()));
    }

    /**
     * @param DentalExamination $dentalExamination
     * @param DentalExaminationRequest $request
     * @return JsonResponse
     */
    #[OA\Put(
        path: '/api/dental-examination/{uuid}',
        summary: 'Aktualizuje badanie stomatologiczne',
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
                ]
            )
        ),
        tags: ['DentalExamination'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Zaktualizowano'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function update(DentalExamination $dentalExamination, DentalExaminationRequest $request): JsonResponse
    {
        $this->dentalExaminationService->updateDentalExamination($dentalExamination, $request->all());

        return new JsonResponse(null, 204);
    }

    /**
     * @param DentalExamination $dentalExamination
     * @return JsonResponse
     */
    #[OA\Delete(
        path: '/api/dental-examination/{uuid}',
        summary: 'Usuwa badanie stomatologiczne',
        security: [['sanctum' => []]],
        tags: ['DentalExamination'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Usunięto'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function destroy(DentalExamination $dentalExamination): JsonResponse
    {
        $this->dentalExaminationService->deleteDentalExamination($dentalExamination);

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
        path: '/api/dental-examination/export',
        summary: 'Eksport badań stomatologicznych do pliku',
        security: [['sanctum' => []]],
        tags: ['DentalExamination'],
        parameters: [
            new OA\QueryParameter(name: 'type', required: true, schema: new OA\Schema(type: 'string', enum: ['xlsx', 'csv', 'ods'])),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Plik do pobrania', content: new OA\MediaType(mediaType: 'application/octet-stream')),
        ]
    )]
    public function export(ExportRequest $request): BinaryFileResponse
    {
        return $this->dentalExaminationService->export($request);
    }
}
