<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
// use App\Http\Requests\AcceptTermsRequest;
use App\Http\Requests\CurrentTermsRequest;
use App\Http\Resources\TermsAcceptanceResource;
use App\Services\TermsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Api\AcceptTermsRequest;

class TermsController extends Controller
{
    public function __construct(
        private readonly TermsService $termsService
    ) {}

    /**
     * GET /api/terms/status
     * Estado global de aceptación de todos los documentos obligatorios.
     */
    public function status(Request $request): JsonResponse
    {
        $result = $this->termsService->getStatus($request->user());

        return response()->json($result);
    }

    /**
     * GET /api/terms/current?docType=terms
     * Estado de aceptación para un tipo de documento concreto.
     */
    public function current(CurrentTermsRequest $request): JsonResponse
    {
        $result = $this->termsService->getCurrent(
            $request->user(),
            $request->validated('docType')
        );

        return response()->json($result);
    }

    /**
     * POST /api/terms/accept
     * Registra la aceptación de una versión concreta.
     */
    public function accept(AcceptTermsRequest $request): JsonResponse
    {
        $acceptance = $this->termsService->accept(
            user: $request->user(),
            docType: $request->validated('docType'),
            version: $request->validated('version'),
            sourceUrl: $request->validated('sourceUrl'),
            ipAddress: $request->attributes->get('client_ip'),
            userAgent: $request->attributes->get('client_user_agent'),
        );

        return (new TermsAcceptanceResource($acceptance))
            ->response()
            ->setStatusCode(201);
    }
     /**
 * GET /api/terms/version?docType=terms
 * Endpoint PÚBLICO: devuelve la versión vigente del documento.
 * Se usa en la pantalla de registro, antes de que exista usuario.
 */
public function publicVersion(Request $request): JsonResponse
{
    $docType = $request->query('docType', 'terms');
    if (! in_array($docType, ['terms', 'privacy'], true)) {
        return response()->json(['success' => false, 'error' => 'INVALID_DOC_TYPE'], 422);
    }

    $version = \App\Models\LegalDocumentVersion::active($docType)
        ->orderByDesc('effective_date')->orderByDesc('id')
        ->first();

    if (! $version) {
        return response()->json([
            'success' => false, 'error' => 'NO_ACTIVE_VERSION',
            'message' => "No hay versión vigente para '{$docType}'.",
        ], 404);
    }

    return response()->json([
        'docType'       => $docType,
        'version'       => $version->version,
        'contentHash'   => $version->content_hash,
        'effectiveDate' => $version->effective_date->toIso8601String(),
    ]);
}
}