<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use App\Http\Requests\Api\RegisterRequest;
use App\Models\LegalDocumentVersion;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class AuthController extends Controller
{
    public function __construct(
        private FirebaseService $firebase
    ) {}

    
    public function exchange(Request $request): JsonResponse
    {
        // $request->validate(['id_token' => ['required', 'string', 'max:4096']]);
// 
        try {
            $claims = $this->firebase->verifyIdToken($request->input('id_token'));
        } catch (FailedToVerifyToken $e) {
            Log::warning('exchange: token inválido');
            return response()->json([
                'success' => false, 'code' => 'INVALID_TOKEN',
                'message' => 'Token de Firebase inválido o expirado.',
            ], 401);
        } catch (\Throwable $e) {
            Log::error('exchange: error verificando token', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false, 'code' => 'AUTH_UNAVAILABLE',
                'message' => 'No se pudo verificar la sesión. Inténtalo de nuevo.',
            ], 503);
        }

        $uid      = $claims['uid'];
        $email    = mb_strtolower($claims['email'] ?? '');
        $verified = (bool) ($claims['email_verified'] ?? false);

        $user = User::where('firebase_localId', $uid)->first();

        // Solo enlazar por correo usuarios antiguos sin localId y solo si Firebase verificó el correo
        if (! $user && $verified && $email !== '') {
            $user = User::where('email', $email)->whereNull('firebase_localId')->first();
        }

        if (! $user) {
            return response()->json([
                'success' => false, 'code' => 'USER_NOT_REGISTERED',
                'message' => 'Usuario no encontrado en el sistema.',
            ], 404);
        }

        if (empty($user->firebase_localId)) {
            $user->firebase_localId = $uid;
        }
        if ($verified && ! $user->email_verified_at) {
            $user->email_verified_at = now();
        }
        if ($user->isDirty()) {
            $user->save();
        }

        $user->tokens()->delete();   // una sola sesión activa; quítalo si quieres multidispositivo
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token'   => $token,
            'user'    => [
                'id'             => $user->id,
                'name'           => $user->name,
                'last_name'      => $user->last_name,
                'email'          => $user->email,
                'is_admin'       => (bool) $user->is_admin,
                'email_verified' => (bool) $user->email_verified_at,
            ],
        ]);
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

    public function register(RegisterRequest $request): JsonResponse
    {
      console.log('firebaseWeb:', environment.firebaseWeb);

        try {

            $claims = $this->firebase->verifyIdToken($request->input('id_token'));
        } catch (FailedToVerifyToken $e) {
            return response()->json([
                'success' => false, 'code' => 'INVALID_TOKEN',
                'message' => 'Token de Firebase inválido o expirado.',
            ], 401);
        } catch (\Throwable $e) {
            Log::error('register: error verificando token', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false, 'code' => 'AUTH_UNAVAILABLE',
                'message' => 'No se pudo verificar la cuenta. Inténtalo de nuevo.',
            ], 503);
        }

        $uid   = $claims['uid'];
        $email = mb_strtolower($claims['email'] ?? '');
        if ($email === '') {
            return response()->json(['success' => false, 'code' => 'NO_EMAIL',
                'message' => 'La cuenta no tiene correo.'], 422);
        }

        // Lo que el usuario vio debe coincidir con la versión vigente HOY en el servidor
        $versions = [];
        foreach ($request->input('accepted_documents') as $doc) {
            $active = LegalDocumentVersion::active($doc['doc_type'])
                ->orderByDesc('effective_date')->orderByDesc('id')->first();

            if (! $active
                || $active->version !== $doc['version']
                || ! hash_equals(strtolower($active->content_hash), strtolower($doc['content_hash']))) {
                return response()->json([
                    'success' => false, 'code' => 'TERMS_VERSION_OUTDATED',
                    'message' => 'Los términos cambiaron. Recarga la página y vuelve a aceptarlos.',
                ], 409);
            }
            $versions[] = $active;
        }

        try {
            // Log::info('entra en transaction ');
            $name = trim((string) ($request->input('name') ?: ($claims['name'] ?? '')));
            if ($name === '') {
                $name = Str::before($email, '@');
            }
            $name = Str::limit($name, 50, '');
            $user = DB::transaction(function () use ($request, $claims, $uid, $email, $versions) {
                // Idempotente: si un reintento llega tras un fallo parcial, completa lo que falte
                $user = User::where('firebase_localId', $uid)->lockForUpdate()->first();

                if (! $user) {
                    if (User::where('email', $email)->exists()) {
                        throw new \DomainException('EMAIL_TAKEN');
                    }

                    $user = new User();                 // sin password, sin is_admin
                    $user->name              = $name;
                    $user->email             = $email;
                    $user->firebase_localId  = $uid;
                    $user->email_verified_at = ! empty($claims['email_verified']) ? now() : null;
                    // $user->registered_at     = now();
                    // $user->registration_ip   = $request->attributes->get('client_ip');
                    $user->save();
                }

                foreach ($versions as $v) {
                    DB::table('terms_acceptance_log')->insertOrIgnore([
                        'user_id'        => $user->id,
                        'doc_version_id' => $v->id,
                        'accepted_at'    => now(),
                        'ip_address'     => $request->attributes->get('client_ip'),
                        'user_agent'     => Str::limit((string) $request->attributes->get('client_user_agent'), 1000, ''),
                        'source_url'     => Str::limit((string) $request->headers->get('referer'), 1000, ''),
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                }

                return $user;
            });
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'code' => 'EMAIL_TAKEN',
                'message' => 'Ese correo ya está registrado.'], 409);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json(['success' => false, 'code' => 'ALREADY_EXISTS',
                    'message' => 'La cuenta ya existe.'], 409);
            }
            Log::error('register failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'code' => 'REGISTER_ERROR',
                'message' => 'No se pudo completar el registro.'], 500);
        }

        return response()->json(['success' => true, 'user' => ['id' => $user->id]], 201);
    }
}



/**
     * POST /api/auth/exchange
     * Recibe un idToken de Firebase y devuelve un token de Sanctum.
     */
    // public function exchange(Request $request): JsonResponse
    // {
    //     $request->validate([
    //         'id_token' => ['required', 'string'],
    //     ]);

    //     /** validar que el usuario tenga el email verificado  */

    //     // if (empty($user->email_verified_at)) {
    //     //     return response()->json([
    //     //         'success' => false,
    //     //         'error' => true,
    //     //         'message'   => 'Debes confirmar tu email antes de continuar.',
    //     //             ], 403);
    //     // }

    //     try {
    //         // 1. Verificar el token con Firebase
    //         $claims = $this->firebase->verifyIdToken($request->input('id_token'));

    //         $firebaseUid = $claims['uid'];   // = localId
    //         $email       = $claims['email'];

    //         // 2. Buscar o crear el usuario en tu BD
    //         $user = User::where('firebase_localId', $firebaseUid)->first();

    //         if (!$user) {
    //             // Fallback: buscar por email
    //             $user = User::where('email', $email)->first();
    //         }

    //         if (!$user) {
    //             return response()->json([
    //                 'success' => false,
    //                 'error'   => 'Usuario no encontrado en el sistema.',
    //             ], 404);
    //         }

    //         // 3. Actualizar firebase_localId si falta
    //         if (empty($user->firebase_localId)) {
    //             $user->firebase_localId = $firebaseUid;
    //             $user->save();
    //         }

    //         // 4. (Opcional) revocar tokens anteriores para no acumular
    //         $user->tokens()->delete();

    //         // 5. Crear token de Sanctum
    //         $token = $user->createToken('api-token')->plainTextToken;

    //         return response()->json([
    //             'success' => true,
    //             'token'   => $token,
    //             'user'    => [
    //                 'id'       => $user->id,
    //                 'name'     => $user->name,
    //                 'last_name'=> $user->last_name,
    //                 'email'    => $user->email,
    //                 'is_admin' => (bool) $user->is_admin,
    //             ],
    //         ]);

    //    } catch (FailedToVerifyToken $e) {
    //          Log::error('❌ FailedToVerifyToken', [
    //             'message' => $e->getMessage(),
    //              'token_preview' => substr($request->input('id_token'), 0, 50) . '...',
    //              ]);

    //              return response()->json([
    //                     'success' => false,
    //                  'error'   => 'Token de Firebase inválido o expirado.',
    //              ], 401);
    //     } catch (\Throwable $e) {
    //         Log::error('Firebase exchange error', ['error' => $e->getMessage()]);

    //         return response()->json([
    //             'success' => false,
    //             'debug'   => config('app.debug') ? [
    //                 'message' => $e->getMessage(),
    //                 'file'    => $e->getFile().':'.$e->getLine(),
    //     ]           : null,
    //             'error'   => 'Error procesando el tokenn',
    //         ], 500);
    //     }
    // }
