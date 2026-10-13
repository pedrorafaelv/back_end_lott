<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ViaCollection;
use App\Http\Resources\ViaResource;
use App\Models\Via;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class ViaController extends Controller
{
    /**
     * GET /api/vias
     *
     * Lista todas las vías con filtros opcionales.
     */
    public function index(Request $request): ViaCollection
    {
        $query = Via::query()->ordered();

        // Filtro por tipo: ?type=credit|debit|neutral
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Filtro por estado: ?active=1
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

       

        // Búsqueda por label/code: ?search=deposito
        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('label', 'like', "%{$term}%")
                  ->orWhere('code', 'like', "%{$term}%");
            });
        }

        return new ViaCollection($query->get());
    }

    /**
     * GET /api/vias/credits
     * Solo las vías que suman saldo.
     */
    public function credits(): ViaCollection
    {
        return new ViaCollection(
            Via::query()->active()->credits()->ordered()->get()
        );
    }

    /**
     * GET /api/vias/debits
     * Solo las vías que restan saldo.
     */
    public function debits(): ViaCollection
    {
        return new ViaCollection(
            Via::query()->active()->debits()->ordered()->get()
        );
    }

    /**
     * GET /api/vias/neutral
     * Solo las vías neutrales.
     */
    public function neutral(): ViaCollection
    {
        return new ViaCollection(
            Via::query()->active()->neutral()->ordered()->get()
        );
    }

    public function creditsCanBeSeeByUser(): viaCollection
    {
        return new ViaCollection (
            Via::query()->active()->credits()->CanSeeByUser('1')->get()
        ); 
    }

    public function debitsCanBeSeeByUser() :viaCollection
    {
         return new ViaCollection (
            Via::query()->active()->debits()->CanSeeByUser('1')->get()
        ); 
    }

    /**
     * POST /api/vias
     *
     * Crea una nueva vía.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code'          => ['required', 'string', 'max:30', 'unique:vias,code', 'alpha_dash'],
            'label'         => ['required', 'string', 'max:255'],
            'icon'          => ['nullable', 'string', 'max:255'],
            'color'         => ['nullable', 'string', 'max:20', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'type'          => ['required', Rule::in(['credit', 'debit', 'neutral'])],
            'is_active'     => ['boolean'],
            'display_order' => ['integer', 'min:0'],
        ]);

        $via = Via::create($data);

        return (new ViaResource($via))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/vias/{via}
     *
     * Detalle de una vía.
     */
    public function show(Via $via): ViaResource
    {
        return new ViaResource($via);
    }

    /**
     * GET /api/vias/code/{code}
     *
     * Busca una vía por su código único.
     * Útil para el frontend: /api/vias/code/withdrawal
     */
    public function showByCode(string $code): ViaResource
    {
        $via = Via::where('code', $code)->firstOrFail();

        return new ViaResource($via);
    }

    /**
     * PUT/PATCH /api/vias/{via}
     *
     * Actualiza una vía existente.
     */
    public function update(Request $request, Via $via): ViaResource
    {
        $data = $request->validate([
            'code'          => ['sometimes', 'string', 'max:30', 'alpha_dash',
                                Rule::unique('vias', 'code')->ignore($via->id)],
            'label'         => ['sometimes', 'string', 'max:255'],
            'icon'          => ['nullable', 'string', 'max:255'],
            'color'         => ['nullable', 'string', 'max:20', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'type'          => ['sometimes', Rule::in(['credit', 'debit', 'neutral'])],
            'is_active'     => ['boolean'],
            'display_order' => ['integer', 'min:0'],
        ]);

        $via->update($data);

        return new ViaResource($via);
    }

    /**
     * DELETE /api/vias/{via}
     *
     * Elimina una vía (soft delete recomendado si hay histórico).
     */
    public function destroy(Via $via): JsonResponse
    {
        // ⚠️ Peligro: si hay movimientos referenciando esta vía, la FK fallará.
        // Recomendación: validar antes o usar ->is_active = false en vez de borrar.
        if ($via->movements()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar: hay movimientos asociados a esta vía.',
            ], 422);
        }

        $via->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vía eliminada correctamente.',
        ]);
    }

    /**
     * PATCH /api/vias/{via}/toggle
     *
     * Activa/desactiva una vía sin eliminarla.
     */
    public function toggle(Via $via): ViaResource
    {
        $via->update(['is_active' => !$via->is_active]);

        return new ViaResource($via);
    }
}