<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExportRequest;
use App\Services\AuditServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Summary of AuditableController
 */
class AuditableController extends Controller
{
    /**
     * @param AuditServiceInterface $auditService
     */
    public function __construct(
        private readonly AuditServiceInterface $auditService
    ) {}

    /**
     * @param string $resource
     * @param string $uuid
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/{resource}/{uuid}/history',
        summary: 'Historia zmian danego rekordu',
        security: [['sanctum' => []]],
        tags: ['Auditable'],
        parameters: [
            new OA\PathParameter(name: 'resource', schema: new OA\Schema(type: 'string', enum: ['user', 'patient', 'calendar', 'dental-examination', 'material', 'job-position'])),
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
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/AuditResource')),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function index(string $resource, string $uuid): LengthAwarePaginator
    {
        return $this->auditService->getHistory($this->auditService->resolveAuditable($resource, $uuid));
    }

    /**
     * @param string $resource
     * @param string $uuid
     * @param ExportRequest $request
     * @return BinaryFileResponse
     *
     * @throws Exception
     * @throws WriterException
     */
    #[OA\Get(
        path: '/api/{resource}/{uuid}/history/export',
        summary: 'Eksport historii zmian danego rekordu do pliku',
        security: [['sanctum' => []]],
        tags: ['Auditable'],
        parameters: [
            new OA\PathParameter(name: 'resource', schema: new OA\Schema(type: 'string', enum: ['user', 'patient', 'calendar', 'dental-examination', 'material', 'job-position'])),
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\QueryParameter(name: 'type', required: true, schema: new OA\Schema(type: 'string', enum: ['xlsx', 'csv', 'ods'])),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Plik do pobrania', content: new OA\MediaType(mediaType: 'application/octet-stream')),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function export(string $resource, string $uuid, ExportRequest $request): BinaryFileResponse
    {
        return $this->auditService->exportHistory($this->auditService->resolveAuditable($resource, $uuid), $request);
    }
}
