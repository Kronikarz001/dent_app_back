<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use OpenApi\Attributes as OA;

#[OA\Info(version: '1.0.0', description: 'API aplikacji DentApp', title: 'DentApp API')]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    description: 'Token Sanctum — przekaż w nagłówku: Authorization: Bearer {token}',
    bearerFormat: 'JWT',
    scheme: 'bearer'
)]
#[OA\Schema(
    schema: 'PaginatedResponse',
    properties: [
        new OA\Property(property: 'current_page', type: 'integer'),
        new OA\Property(property: 'last_page', type: 'integer'),
        new OA\Property(property: 'per_page', type: 'integer'),
        new OA\Property(property: 'total', type: 'integer'),
        new OA\Property(property: 'from', type: 'integer'),
        new OA\Property(property: 'to', type: 'integer'),
    ]
)]
#[OA\Schema(
    schema: 'FileResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'filename', type: 'string'),
        new OA\Property(property: 'extension', type: 'string'),
        new OA\Property(property: 'size', type: 'integer'),
        new OA\Property(property: 'mimetype', type: 'string'),
        new OA\Property(property: 'path', type: 'string'),
        new OA\Property(property: 'is_latest', type: 'boolean'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'PhoneNumberResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'number', type: 'string'),
        new OA\Property(property: 'type', type: 'string', enum: ['PRIVATE', 'WORK']),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'UserResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'first_name', type: 'string'),
        new OA\Property(property: 'last_name', type: 'string'),
        new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true),
        new OA\Property(property: 'private_email', type: 'string', format: 'email'),
        new OA\Property(property: 'pesel', type: 'string', nullable: true),
        new OA\Property(property: 'is_active', type: 'boolean'),
        new OA\Property(property: 'avatar_path', type: 'string', nullable: true),
        new OA\Property(property: 'background_path', type: 'string', nullable: true),
        new OA\Property(property: 'private_phone_number', type: 'string', nullable: true),
        new OA\Property(property: 'phone_number', type: 'string', nullable: true),
        new OA\Property(property: 'job_position', ref: '#/components/schemas/JobPositionResource', nullable: true),
        new OA\Property(property: 'street', type: 'string', nullable: true),
        new OA\Property(property: 'house_number', type: 'string', nullable: true),
        new OA\Property(property: 'apartment_number', type: 'string', nullable: true),
        new OA\Property(property: 'postal_code', type: 'string', nullable: true),
        new OA\Property(property: 'city', type: 'string', nullable: true),
        new OA\Property(property: 'status', type: 'string', enum: ['ACTIVE', 'NON_ACTIVE'], description: 'ACTIVE gdy użytkownik ma aktualnie ważny token'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'LoggedUserResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'first_name', type: 'string'),
        new OA\Property(property: 'last_name', type: 'string'),
        new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true),
        new OA\Property(property: 'private_email', type: 'string', format: 'email'),
        new OA\Property(property: 'pesel', type: 'string', nullable: true),
        new OA\Property(property: 'is_active', type: 'boolean'),
        new OA\Property(property: 'avatar_path', type: 'string', nullable: true),
        new OA\Property(property: 'private_phone_number', type: 'string', nullable: true),
        new OA\Property(property: 'phone_number', type: 'string', nullable: true),
        new OA\Property(property: 'job_position', ref: '#/components/schemas/JobPositionResource', nullable: true),
        new OA\Property(property: 'street', type: 'string', nullable: true),
        new OA\Property(property: 'house_number', type: 'string', nullable: true),
        new OA\Property(property: 'apartment_number', type: 'string', nullable: true),
        new OA\Property(property: 'postal_code', type: 'string', nullable: true),
        new OA\Property(property: 'city', type: 'string', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'PatientResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'first_name', type: 'string'),
        new OA\Property(property: 'last_name', type: 'string'),
        new OA\Property(property: 'email', type: 'string', format: 'email'),
        new OA\Property(property: 'pesel', type: 'string'),
        new OA\Property(property: 'is_active', type: 'boolean'),
        new OA\Property(property: 'phone_number', type: 'array', items: new OA\Items(ref: '#/components/schemas/PhoneNumberResource')),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'JobPositionResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'CalendarResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'type', type: 'string'),
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'date', type: 'string', format: 'date'),
        new OA\Property(property: 'end_date', type: 'string', format: 'date', nullable: true),
        new OA\Property(property: 'start_time', type: 'string', nullable: true),
        new OA\Property(property: 'end_time', type: 'string', nullable: true),
        new OA\Property(property: 'no_show', type: 'boolean'),
        new OA\Property(property: 'is_active', type: 'boolean'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'EmployeeScheduleResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'type', type: 'string'),
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'date', type: 'string', format: 'date'),
        new OA\Property(property: 'end_date', type: 'string', format: 'date', nullable: true),
        new OA\Property(property: 'start_time', type: 'string', nullable: true),
        new OA\Property(property: 'end_time', type: 'string', nullable: true),
        new OA\Property(property: 'is_active', type: 'boolean'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'DentalExaminationResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'short_description', type: 'string', nullable: true),
        new OA\Property(property: 'price', type: 'integer', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'MaterialResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'short_description', type: 'string', nullable: true),
        new OA\Property(property: 'price', type: 'integer', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'CompanyResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
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
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'MessageResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'user_uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'recipient_user_uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'message_group_uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'unread', type: 'boolean', description: 'Czy zalogowany użytkownik jeszcze nie przeczytał tej wiadomości'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'AuditResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'auditable_type', type: 'string'),
        new OA\Property(property: 'auditable_id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'user_uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'type', type: 'string', enum: ['CREATE', 'UPDATE', 'DELETE']),
        new OA\Property(property: 'change_from', type: 'object', nullable: true),
        new OA\Property(property: 'change_to', type: 'object', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'PermissionResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'resource', type: 'string'),
        new OA\Property(property: 'type', type: 'string', enum: ['view', 'edit']),
        new OA\Property(property: 'name', type: 'string', description: '{resource}.{type}'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'PermissionGroupResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'permissions', type: 'array', items: new OA\Items(ref: '#/components/schemas/PermissionResource')),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'UserGroupResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'RoleResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'DepartmentResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
abstract class Controller
{
    /**
     * Guards *FileController endpoints against IDOR: a route-bound File must
     * actually belong to the route-bound parent resource, not just exist.
     *
     * @param File $file
     * @param Model $parent
     * @return void
     *
     * @throws ModelNotFoundException
     */
    protected function assertFileBelongsTo(File $file, Model $parent): void
    {
        if ($file->fileable_type !== $parent->getMorphClass() || $file->fileable_id !== $parent->uuid) {
            throw (new ModelNotFoundException)->setModel(File::class, [$file->uuid]);
        }
    }
}
