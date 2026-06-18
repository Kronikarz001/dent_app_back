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
     * @param string $uuid
     * @param string $resource
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/user/{uuid}/history',
        summary: 'Historia zmian użytkownika',
        security: [['sanctum' => []]],
        tags: ['Auditable'],
        parameters: [new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid'))],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/AuditResource'))]),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    #[OA\Get(
        path: '/api/patient/{uuid}/history',
        summary: 'Historia zmian pacjenta',
        security: [['sanctum' => []]],
        tags: ['Auditable'],
        parameters: [new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid'))],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/AuditResource'))]),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    #[OA\Get(
        path: '/api/calendar/{uuid}/history',
        summary: 'Historia zmian wydarzenia w kalendarzu',
        security: [['sanctum' => []]],
        tags: ['Auditable'],
        parameters: [new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid'))],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/AuditResource'))]),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    #[OA\Get(
        path: '/api/dental-examination/{uuid}/history',
        summary: 'Historia zmian badania stomatologicznego',
        security: [['sanctum' => []]],
        tags: ['Auditable'],
        parameters: [new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid'))],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/AuditResource'))]),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    #[OA\Get(
        path: '/api/material/{uuid}/history',
        summary: 'Historia zmian materiału',
        security: [['sanctum' => []]],
        tags: ['Auditable'],
        parameters: [new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid'))],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/AuditResource'))]),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    #[OA\Get(
        path: '/api/job-position/{uuid}/history',
        summary: 'Historia zmian stanowiska',
        security: [['sanctum' => []]],
        tags: ['Auditable'],
        parameters: [new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid'))],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/AuditResource'))]),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function index(string $uuid, string $resource): LengthAwarePaginator
    {
        return $this->auditService->getHistory($this->auditService->resolveAuditable($resource, $uuid));
    }

    /**
     * @param string $uuid
     * @param string $resource
     * @param ExportRequest $request
     * @return BinaryFileResponse
     *
     * @throws Exception
     * @throws WriterException
     */
    #[OA\Get(
        path: '/api/user/{uuid}/history/export',
        summary: 'Eksport historii zmian użytkownika',
        security: [['sanctum' => []]],
        tags: ['Auditable'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\QueryParameter(name: 'type', required: true, schema: new OA\Schema(type: 'string', enum: ['xlsx', 'csv', 'ods'])),
        ],
        responses: [new OA\Response(response: 200, description: 'Plik do pobrania'), new OA\Response(response: 404, description: 'Nie znaleziono')]
    )]
    #[OA\Get(
        path: '/api/patient/{uuid}/history/export',
        summary: 'Eksport historii zmian pacjenta',
        security: [['sanctum' => []]],
        tags: ['Auditable'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\QueryParameter(name: 'type', required: true, schema: new OA\Schema(type: 'string', enum: ['xlsx', 'csv', 'ods'])),
        ],
        responses: [new OA\Response(response: 200, description: 'Plik do pobrania'), new OA\Response(response: 404, description: 'Nie znaleziono')]
    )]
    #[OA\Get(
        path: '/api/calendar/{uuid}/history/export',
        summary: 'Eksport historii zmian wydarzenia w kalendarzu',
        security: [['sanctum' => []]],
        tags: ['Auditable'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\QueryParameter(name: 'type', required: true, schema: new OA\Schema(type: 'string', enum: ['xlsx', 'csv', 'ods'])),
        ],
        responses: [new OA\Response(response: 200, description: 'Plik do pobrania'), new OA\Response(response: 404, description: 'Nie znaleziono')]
    )]
    #[OA\Get(
        path: '/api/dental-examination/{uuid}/history/export',
        summary: 'Eksport historii zmian badania stomatologicznego',
        security: [['sanctum' => []]],
        tags: ['Auditable'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\QueryParameter(name: 'type', required: true, schema: new OA\Schema(type: 'string', enum: ['xlsx', 'csv', 'ods'])),
        ],
        responses: [new OA\Response(response: 200, description: 'Plik do pobrania'), new OA\Response(response: 404, description: 'Nie znaleziono')]
    )]
    #[OA\Get(
        path: '/api/material/{uuid}/history/export',
        summary: 'Eksport historii zmian materiału',
        security: [['sanctum' => []]],
        tags: ['Auditable'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\QueryParameter(name: 'type', required: true, schema: new OA\Schema(type: 'string', enum: ['xlsx', 'csv', 'ods'])),
        ],
        responses: [new OA\Response(response: 200, description: 'Plik do pobrania'), new OA\Response(response: 404, description: 'Nie znaleziono')]
    )]
    #[OA\Get(
        path: '/api/job-position/{uuid}/history/export',
        summary: 'Eksport historii zmian stanowiska',
        security: [['sanctum' => []]],
        tags: ['Auditable'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\QueryParameter(name: 'type', required: true, schema: new OA\Schema(type: 'string', enum: ['xlsx', 'csv', 'ods'])),
        ],
        responses: [new OA\Response(response: 200, description: 'Plik do pobrania'), new OA\Response(response: 404, description: 'Nie znaleziono')]
    )]
    public function export(string $uuid, string $resource, ExportRequest $request): BinaryFileResponse
    {
        return $this->auditService->exportHistory($this->auditService->resolveAuditable($resource, $uuid), $request);
    }
}
