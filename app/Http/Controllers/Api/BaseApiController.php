<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

abstract class BaseApiController extends Controller
{
    /**
     * Respuesta exitosa estándar
     */
    protected function successResponse(
        string $code,
        string $message,
        $data = null,
        int $httpStatus = 200,
        ?string $requestId = null
    ) {
        return response()->json([
            'success' => true,
            'code'    => $code,
            'message' => $message,
            'data'    => $data,
            'errors'  => null,
            'meta'    => [
                'timestamp'  => now()->toDateTimeString(),
                'request_id' => $requestId ?? (string) Str::uuid(),
                'version'    => '1.0',
            ],
        ], $httpStatus);
    }

    /**
     * Respuesta de error estándar
     */
    protected function errorResponse(
        string $code,
        string $message,
        $errors = null,
        int $httpStatus = 400,
        ?string $requestId = null
    ) {
        return response()->json([
            'success' => false,
            'code'    => $code,
            'message' => $message,
            'data'    => null,
            'errors'  => $errors,
            'meta'    => [
                'timestamp'  => now()->toDateTimeString(),
                'request_id' => $requestId ?? (string) Str::uuid(),
                'version'    => '1.0',
            ],
        ], $httpStatus);
    }
}