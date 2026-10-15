<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        // ──────────────────────────────────────────────────────
        // 1. Autenticación (existente, sin cambios funcionales)
        // ──────────────────────────────────────────────────────
        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => 'No autenticado. Token ausente o inválido.',
                ], 401);
            }
        });

        // ──────────────────────────────────────────────────────
        // 2. Versión de términos no vigente (NUEVO)
        // ──────────────────────────────────────────────────────
        $this->renderable(function (VersionNotActiveException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => 'VERSION_NOT_ACTIVE',
                    'message' => $e->getMessage(),
                ], 400);
            }
        });
    }

    // public function register()
    // {
    //     $this->reportable(function (Throwable $e) {
    //         //
    //     });
    // }
}