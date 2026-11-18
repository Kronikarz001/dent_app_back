<?php

namespace App\Http\Controllers;

use App\Traits\AssertsFileOwnership;
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
    schema: 'SelectListItem',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', type: 'string'),
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
    schema: 'PhotoResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'filename', type: 'string'),
        new OA\Property(property: 'extension', type: 'string'),
        new OA\Property(property: 'size', type: 'integer'),
        new OA\Property(property: 'mimetype', type: 'string'),
        new OA\Property(property: 'path', type: 'string'),
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
        new OA\Property(property: 'gender', description: 'Wyliczana z numeru PESEL; null gdy PESEL nie jest podany', type: 'string', nullable: true, enum: ['M', 'F']),
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
        new OA\Property(property: 'status', description: 'ACTIVE gdy użytkownik ma aktualnie ważny token', type: 'string', enum: ['ACTIVE', 'NON_ACTIVE']),
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
        new OA\Property(property: 'gender', type: 'string', enum: ['M', 'F'], nullable: true, description: 'Wyliczana z numeru PESEL; null gdy PESEL nie jest podany'),
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
        new OA\Property(property: 'street', type: 'string', nullable: true),
        new OA\Property(property: 'house_number', type: 'string', nullable: true),
        new OA\Property(property: 'apartment_number', type: 'string', nullable: true),
        new OA\Property(property: 'postal_code', type: 'string', nullable: true),
        new OA\Property(property: 'city', type: 'string', nullable: true),
        new OA\Property(property: 'gender', type: 'string', enum: ['MALE', 'FEMALE'], nullable: true),
        new OA\Property(property: 'notes', type: 'string', nullable: true),
        new OA\Property(property: 'doctor', ref: '#/components/schemas/UserResource', nullable: true),
        new OA\Property(property: 'romexis_patient_id', type: 'string', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'PatientRomexisSyncResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'romexis_patient_id', type: 'string'),
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
        new OA\Property(property: 'users', type: 'array', items: new OA\Items(ref: '#/components/schemas/PersonSummaryResource')),
        new OA\Property(property: 'patients', type: 'array', items: new OA\Items(ref: '#/components/schemas/PersonSummaryResource')),
        new OA\Property(property: 'dental_examinations', type: 'array', items: new OA\Items(ref: '#/components/schemas/DentalExaminationResource')),
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
        new OA\Property(property: 'users', type: 'array', items: new OA\Items(ref: '#/components/schemas/PersonSummaryResource')),
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
    schema: 'MedicineResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'ean_gtin', type: 'string', nullable: true),
        new OA\Property(property: 'reimbursement_level', type: 'string', enum: ['FULL', 'FLAT_RATE', 'FREE', 'R', 'OTHER']),
        new OA\Property(property: 'external_reference', type: 'string', nullable: true, description: 'Pole przygotowane pod przyszłą integrację z Medfile/P1'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'PatientStatisticsDoctor',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'first_name', type: 'string'),
        new OA\Property(property: 'last_name', type: 'string'),
        new OA\Property(property: 'position', type: 'string', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'PatientStatisticsVisit',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'starts_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'doctor', ref: '#/components/schemas/PatientStatisticsDoctor', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'PatientStatisticsResource',
    properties: [
        new OA\Property(property: 'last_visit', ref: '#/components/schemas/PatientStatisticsVisit', nullable: true),
        new OA\Property(property: 'next_visit', ref: '#/components/schemas/PatientStatisticsVisit', nullable: true),
        new OA\Property(property: 'no_shows', properties: [
            new OA\Property(property: 'count', type: 'integer'),
            new OA\Property(property: 'total_appointments', type: 'integer'),
            new OA\Property(property: 'last_at', type: 'string', format: 'date-time', nullable: true),
        ], type: 'object'),
        new OA\Property(property: 'lead_doctor', properties: [
            new OA\Property(property: 'doctor', ref: '#/components/schemas/PatientStatisticsDoctor'),
            new OA\Property(property: 'since', type: 'string', format: 'date', nullable: true),
        ], type: 'object', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'PrescriptionResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'code', type: 'string', nullable: true),
        new OA\Property(property: 'status', type: 'string', enum: ['DRAFT', 'ISSUED', 'REALIZED', 'CANCELLED', 'EXPIRED']),
        new OA\Property(property: 'patient_uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'doctor_uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'external_reference', type: 'string', nullable: true, description: 'Pole przygotowane pod przyszłą integrację z Medfile/P1'),
        new OA\Property(property: 'patient', ref: '#/components/schemas/PatientResource', nullable: true),
        new OA\Property(property: 'doctor', ref: '#/components/schemas/UserResource', nullable: true),
        new OA\Property(property: 'medicines', type: 'array', items: new OA\Items(ref: '#/components/schemas/MedicineResource')),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
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
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'permissions', type: 'array', items: new OA\Items(ref: '#/components/schemas/PermissionResource')),
        new OA\Property(property: 'users', type: 'array', items: new OA\Items(ref: '#/components/schemas/PersonSummaryResource')),
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
#[OA\RequestBody(
    request: 'FileUploadRequest',
    required: true,
    content: new OA\MediaType(
        mediaType: 'multipart/form-data',
        schema: new OA\Schema(
            required: ['files[]'],
            properties: [
                new OA\Property(property: 'files[]', type: 'array', items: new OA\Items(type: 'string', format: 'binary')),
            ]
        )
    )
)]
#[OA\RequestBody(
    request: 'SingleFileUploadRequest',
    required: true,
    content: new OA\MediaType(
        mediaType: 'multipart/form-data',
        schema: new OA\Schema(
            required: ['file'],
            properties: [
                new OA\Property(property: 'file', type: 'string', format: 'binary'),
            ]
        )
    )
)]
#[OA\RequestBody(
    request: 'FileRenameRequest',
    required: true,
    content: new OA\JsonContent(
        required: ['filename'],
        properties: [
            new OA\Property(property: 'filename', type: 'string', example: 'nowa_nazwa'),
        ]
    )
)]
#[OA\RequestBody(
    request: 'NameRequest',
    required: true,
    content: new OA\JsonContent(
        required: ['name'],
        properties: [
            new OA\Property(property: 'name', type: 'string'),
        ]
    )
)]
#[OA\RequestBody(
    request: 'PermissionGroupRequest',
    required: true,
    content: new OA\JsonContent(
        required: ['name'],
        properties: [
            new OA\Property(property: 'name', type: 'string', maxLength: 255),
            new OA\Property(property: 'description', type: 'string', maxLength: 1000, nullable: true),
        ]
    )
)]
#[OA\RequestBody(
    request: 'AssignPermissionsRequest',
    required: true,
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'permissions', type: 'array', items: new OA\Items(type: 'string', format: 'uuid')),
            new OA\Property(property: 'permission_groups', type: 'array', items: new OA\Items(type: 'string', format: 'uuid')),
            new OA\Property(property: 'expires_at', type: 'string', format: 'date-time', nullable: true),
        ]
    )
)]
#[OA\RequestBody(
    request: 'AssignUsersRequest',
    required: true,
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'users', type: 'array', items: new OA\Items(
                properties: [
                    new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'is_manager', type: 'boolean'),
                ]
            )),
        ]
    )
)]
#[OA\RequestBody(
    request: 'AssignJobPositionsRequest',
    required: true,
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'job_positions', type: 'array', items: new OA\Items(type: 'string', format: 'uuid')),
        ]
    )
)]
#[OA\RequestBody(
    request: 'DelegatePermissionRequest',
    required: true,
    content: new OA\JsonContent(
        required: ['user_uuid'],
        properties: [
            new OA\Property(property: 'user_uuid', type: 'string', format: 'uuid'),
            new OA\Property(property: 'permission_uuid', type: 'string', format: 'uuid', nullable: true),
            new OA\Property(property: 'permission_group_uuid', type: 'string', format: 'uuid', nullable: true),
            new OA\Property(property: 'expires_at', type: 'string', format: 'date-time', nullable: true),
        ]
    )
)]
#[OA\RequestBody(
    request: 'ManagedRoleRequest',
    required: true,
    content: new OA\JsonContent(
        required: ['name'],
        properties: [
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(property: 'permissions', type: 'array', items: new OA\Items(type: 'string', format: 'uuid')),
            new OA\Property(property: 'permission_groups', type: 'array', items: new OA\Items(type: 'string', format: 'uuid')),
        ]
    )
)]
#[OA\RequestBody(
    request: 'CompanyRequest',
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
)]
#[OA\RequestBody(
    request: 'EmployeeScheduleRequest',
    required: true,
    content: new OA\JsonContent(
        required: ['type', 'date'],
        properties: [
            new OA\Property(property: 'type', type: 'string'),
            new OA\Property(property: 'name', type: 'string', nullable: true),
            new OA\Property(property: 'description', type: 'string', nullable: true),
            new OA\Property(property: 'date', type: 'string', format: 'date'),
            new OA\Property(property: 'end_date', type: 'string', format: 'date', nullable: true),
            new OA\Property(property: 'start_time', type: 'string', example: '09:00', nullable: true),
            new OA\Property(property: 'end_time', type: 'string', example: '17:00', nullable: true),
            new OA\Property(property: 'is_active', type: 'boolean'),
        ]
    )
)]
#[OA\RequestBody(
    request: 'RoomRequest',
    required: true,
    content: new OA\JsonContent(
        required: ['name'],
        properties: [
            new OA\Property(property: 'name', type: 'string', example: 'Gabinet 1'),
            new OA\Property(property: 'description', type: 'string', nullable: true),
            new OA\Property(property: 'is_active', type: 'boolean'),
        ]
    )
)]
#[OA\RequestBody(
    request: 'ScheduleRuleRequest',
    required: true,
    content: new OA\JsonContent(
        required: ['day', 'start_time', 'end_time'],
        properties: [
            new OA\Property(property: 'day', type: 'string', enum: ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']),
            new OA\Property(property: 'start_time', type: 'string', example: '08:00'),
            new OA\Property(property: 'end_time', type: 'string', example: '16:00'),
        ]
    )
)]
#[OA\RequestBody(
    request: 'PrescriptionRequest',
    required: true,
    content: new OA\JsonContent(
        required: ['status', 'patient_uuid', 'doctor_uuid'],
        properties: [
            new OA\Property(property: 'name', type: 'string', nullable: true),
            new OA\Property(property: 'code', type: 'string', nullable: true),
            new OA\Property(property: 'status', type: 'string', enum: ['DRAFT', 'ISSUED', 'REALIZED', 'CANCELLED', 'EXPIRED']),
            new OA\Property(property: 'patient_uuid', type: 'string', format: 'uuid'),
            new OA\Property(property: 'doctor_uuid', type: 'string', format: 'uuid'),
            new OA\Property(property: 'external_reference', type: 'string', nullable: true),
            new OA\Property(
                property: 'medicines',
                type: 'array',
                items: new OA\Items(properties: [
                    new OA\Property(property: 'medicine_uuid', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'quantity', type: 'integer', example: 1),
                    new OA\Property(property: 'dosage', type: 'string', example: '1 tabletka co 8 godzin'),
                ]),
                nullable: true
            ),
        ]
    )
)]
abstract class Controller
{
    use AssertsFileOwnership;
}
