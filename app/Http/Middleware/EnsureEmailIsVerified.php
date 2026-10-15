<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || ! $user->email_verified_at) {
            return response()->json([
                'success' => false, 'code' => 'EMAIL_NOT_VERIFIED',
                'message' => 'Debes verificar tu correo para realizar esta operación.',
            ], 403);
        }

        return $next($request);
    }
}