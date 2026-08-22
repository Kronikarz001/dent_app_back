<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use App\Services\CompanyServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;

/**
 * Summary of CompanyController
 */
class CompanyController extends Controller
{
    /**
     * @param CompanyServiceInterface $companyService
     */
    public function __construct(
        private readonly CompanyServiceInterface $companyService
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/company',
        summary: 'Lista firm (paginacja)',
        security: [['sanctum' => []]],
        tags: ['Company'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/CompanyResource')),
                        ]),
                    ]
                )
            ),
        ]
    )]
    public function index(): LengthAwarePaginator
    {
        return $this->companyService->getCompanies();
    }

    /**
     * @param Company $company
     * @return CompanyResource
     */
    #[OA\Get(
        path: '/api/company/{uuid}',
        summary: 'Pobiera jedną firmę',
        security: [['sanctum' => []]],
        tags: ['Company'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/CompanyResource')]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function show(Company $company): CompanyResource
    {
        return new CompanyResource($company);
    }

    /**
     * @param CompanyRequest $request
     * @return CompanyResource
     */
    #[OA\Post(
        path: '/api/company',
        summary: 'Tworzy nową firmę',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'regon', 'nip', 'address', 'province', 'district', 'municipality'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'regon', type: 'string'),
                    new OA\Property(property: 'nip', type: 'string'),
                    new OA\Property(property: 'address', type: 'string'),
                    new OA\Property(property: 'province', type: 'string'),
                    new OA\Property(property: 'district', type: 'string'),
                    new OA\Property(property: 'municipality', type: 'string'),
                    new OA\Property(property: 'business_form', type: 'string', nullable: true),
                    new OA\Property(property: 'type_of_business', type: 'string', nullable: true),
                    new OA\Property(property: 'form_of_ownership', type: 'string', nullable: true),
                ]
            )
        ),
        tags: ['Company'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Utworzono',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/CompanyResource')]
                )
            ),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function store(CompanyRequest $request): CompanyResource
    {
        return new CompanyResource($this->companyService->createCompany($request->validated()));
    }

    /**
     * @param CompanyRequest $request
     * @param Company $company
     * @return CompanyResource
     */
    #[OA\Put(
        path: '/api/company/{uuid}',
        summary: 'Aktualizuje firmę',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'regon', 'nip', 'address', 'province', 'district', 'municipality'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'regon', type: 'string'),
                    new OA\Property(property: 'nip', type: 'string'),
                    new OA\Property(property: 'address', type: 'string'),
                    new OA\Property(property: 'province', type: 'string'),
                    new OA\Property(property: 'district', type: 'string'),
                    new OA\Property(property: 'municipality', type: 'string'),
                    new OA\Property(property: 'business_form', type: 'string', nullable: true),
                    new OA\Property(property: 'type_of_business', type: 'string', nullable: true),
                    new OA\Property(property: 'form_of_ownership', type: 'string', nullable: true),
                ]
            )
        ),
        tags: ['Company'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/CompanyResource')]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function update(CompanyRequest $request, Company $company): CompanyResource
    {
        return new CompanyResource($this->companyService->updateCompany($company, $request->validated()));
    }

    /**
     * @param Company $company
     * @return JsonResponse
     */
    #[OA\Delete(
        path: '/api/company/{uuid}',
        summary: 'Usuwa firmę',
        security: [['sanctum' => []]],
        tags: ['Company'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Usunięto'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function destroy(Company $company): JsonResponse
    {
        $this->companyService->deleteCompany($company);

        return new JsonResponse(null, 204);
    }
}
