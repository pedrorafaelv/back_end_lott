<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;

class AuthController extends Controller
{
    public function __construct(
        private FirebaseService $firebase
    ) {}

    /**
     * POST /api/auth/exchange
     * Recibe un idToken de Firebase y devuelve un token de Sanctum.
     */
    public function exchange(Request $request): JsonResponse
    {
        $request->validate([
            'id_token' => ['required', 'string'],
        ]);

        /** validar que el usuario tenga el email verificado  */

        // if (empty($user->email_verified_at)) {
        //     return response()->json([
        //         'success' => false,
        //         'error' => true,
        //         'message'   => 'Debes confirmar tu email antes de continuar.',
        //             ], 403);
        // }

        try {
            // 1. Verificar el token con Firebase
            $claims = $this->firebase->verifyIdToken($request->input('id_token'));

            $firebaseUid = $claims['uid'];   // = localId
            $email       = $claims['email'];

            // 2. Buscar o crear el usuario en tu BD
            $user = User::where('firebase_localId', $firebaseUid)->first();

            if (!$user) {
                // Fallback: buscar por email
                $user = User::where('email', $email)->first();
            }

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Usuario no encontrado en el sistema.',
                ], 404);
            }

            // 3. Actualizar firebase_localId si falta
            if (empty($user->firebase_localId)) {
                $user->firebase_localId = $firebaseUid;
                $user->save();
            }

            // 4. (Opcional) revocar tokens anteriores para no acumular
            $user->tokens()->delete();

            // 5. Crear token de Sanctum
            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'token'   => $token,
                'user'    => [
                    'id'       => $user->id,
                    'name'     => $user->name,
                    'last_name'=> $user->last_name,
                    'email'    => $user->email,
                    'is_admin' => (bool) $user->is_admin,
                ],
            ]);

        } catch (FailedToVerifyToken $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Token de Firebase inválido o expirado.',
            ], 401);
        } catch (\Throwable $e) {
            Log::error('Firebase exchange error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error'   => 'Error procesando el token.',
            ], 500);
        }
    }

    /**
     * POST /api/auth/logout
     * Revoca el token actual.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada.',
        ]);
    }

    /**
     * GET /api/auth/me
     * Devuelve el usuario actual.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'user'    => $request->user(),
        ]);
    }
}