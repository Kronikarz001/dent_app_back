<?php

namespace App\Http\Controllers;

use App\Http\Requests\CalendarRequest;
use App\Http\Requests\ExportRequest;
use App\Http\Resources\CalendarResource;
use App\Models\Calendar;
use App\Services\CalendarServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Summary of CalendarController
 */
class CalendarController extends Controller
{
    /**
     * @param CalendarServiceInterface $calendarService
     */
    public function __construct(
        private readonly CalendarServiceInterface $calendarService
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/calendar',
        summary: 'Lista wydarzeń w kalendarzu',
        security: [['sanctum' => []]],
        tags: ['Calendar'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedResponse'),
                        new OA\Schema(properties: [
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/CalendarResource')),
                        ]),
                    ]
                )
            ),
        ]
    )]
    public function index(): LengthAwarePaginator
    {
        return $this->calendarService->getCalendars();
    }

    /**
     * @return LengthAwarePaginator
     */
    #[OA\Get(
        path: '/api/calendar/selectlist',
        summary: 'Wydarzenia do selecta (uuid + name)',
        security: [['sanctum' => []]],
        tags: ['Calendar'],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
        ]
    )]
    public function selectList(): LengthAwarePaginator
    {
        return $this->calendarService->getCalendarsList();
    }

    /**
     * @param Calendar $calendar
     * @return CalendarResource
     */
    #[OA\Get(
        path: '/api/calendar/{uuid}',
        summary: 'Pobiera jedno wydarzenie',
        security: [['sanctum' => []]],
        tags: ['Calendar'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/CalendarResource')]
                )
            ),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function show(Calendar $calendar): CalendarResource
    {
        return new CalendarResource($calendar);
    }

    /**
     * @param CalendarRequest $request
     * @return CalendarResource
     */
    #[OA\Post(
        path: '/api/calendar',
        summary: 'Tworzy nowe wydarzenie w kalendarzu',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'f_name', 'm_name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Stomatolog'),
                    new OA\Property(property: 'f_name', type: 'string', example: 'Stomatolożka'),
                    new OA\Property(property: 'm_name', type: 'string', example: 'Stomatolodzy'),
                ]
            )
        ),
        tags: ['Calendar'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Utworzono',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/CalendarResource')]
                )
            ),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function store(CalendarRequest $request): CalendarResource
    {
        return new CalendarResource($this->calendarService->createCalendar($request->all()));
    }

    /**
     * @param Calendar $calendar
     * @param CalendarRequest $request
     * @return JsonResponse
     */
    #[OA\Put(
        path: '/api/calendar/{uuid}',
        summary: 'Aktualizuje wydarzenie w kalendarzu',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'f_name', 'm_name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'f_name', type: 'string'),
                    new OA\Property(property: 'm_name', type: 'string'),
                ]
            )
        ),
        tags: ['Calendar'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Zaktualizowano'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
            new OA\Response(response: 422, description: 'Błąd walidacji'),
        ]
    )]
    public function update(Calendar $calendar, CalendarRequest $request): JsonResponse
    {
        $this->calendarService->updateCalendar($calendar, $request->all());

        return new JsonResponse(null, 204);
    }

    /**
     * @param Calendar $calendar
     * @return JsonResponse
     */
    #[OA\Delete(
        path: '/api/calendar/{uuid}',
        summary: 'Usuwa stanowisko',
        security: [['sanctum' => []]],
        tags: ['Calendar'],
        parameters: [
            new OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Usunięto'),
            new OA\Response(response: 404, description: 'Nie znaleziono'),
        ]
    )]
    public function destroy(Calendar $calendar): JsonResponse
    {
        $this->calendarService->deleteCalendar($calendar);

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
        path: '/api/calendar/export',
        summary: 'Eksport wydarzeń do pliku',
        security: [['sanctum' => []]],
        tags: ['Calendar'],
        parameters: [
            new OA\QueryParameter(name: 'type', required: true, schema: new OA\Schema(type: 'string', enum: ['xlsx', 'csv', 'ods'])),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Plik do pobrania', content: new OA\MediaType(mediaType: 'application/octet-stream')),
        ]
    )]
    public function export(ExportRequest $request): BinaryFileResponse
    {
        return $this->calendarService->export($request);
    }
}
