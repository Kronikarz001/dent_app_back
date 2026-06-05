<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="DentApp API",
 *     version="1.0.0",
 *     description="API aplikacji DentApp"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Token Sanctum — przekaż w nagłówku: Authorization: Bearer {token}"
 * )
 *
 * @OA\Schema(
 *     schema="PaginatedResponse",
 *     @OA\Property(property="current_page", type="integer"),
 *     @OA\Property(property="last_page", type="integer"),
 *     @OA\Property(property="per_page", type="integer"),
 *     @OA\Property(property="total", type="integer"),
 *     @OA\Property(property="from", type="integer"),
 *     @OA\Property(property="to", type="integer")
 * )
 *
 * @OA\Schema(
 *     schema="FileResource",
 *     @OA\Property(property="uuid", type="string", format="uuid"),
 *     @OA\Property(property="filename", type="string"),
 *     @OA\Property(property="extension", type="string"),
 *     @OA\Property(property="size", type="integer"),
 *     @OA\Property(property="mimetype", type="string"),
 *     @OA\Property(property="path", type="string"),
 *     @OA\Property(property="is_latest", type="boolean"),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="UserResource",
 *     @OA\Property(property="uuid", type="string", format="uuid"),
 *     @OA\Property(property="first_name", type="string"),
 *     @OA\Property(property="last_name", type="string"),
 *     @OA\Property(property="email", type="string", format="email"),
 *     @OA\Property(property="is_active", type="boolean"),
 *     @OA\Property(property="avatar_url", type="string", nullable=true),
 *     @OA\Property(property="background_url", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="PatientResource",
 *     @OA\Property(property="uuid", type="string", format="uuid"),
 *     @OA\Property(property="first_name", type="string"),
 *     @OA\Property(property="last_name", type="string"),
 *     @OA\Property(property="email", type="string", format="email"),
 *     @OA\Property(property="pesel", type="string"),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="JobPositionResource",
 *     @OA\Property(property="uuid", type="string", format="uuid"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 */
abstract class Controller
{
    //
}
