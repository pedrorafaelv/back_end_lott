<?php

namespace App\Http\Controllers\Admin;   // ✅ correcto

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\DepositRequest;
use App\Models\User;
use App\Models\Via;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use  App\Http\Resources\DepositRequestResource;

class DepositRequestController extends Controller
{
    /**
     * 📋 Listar solicitudes de depósito (Admin)
     * GET /api/admin/deposits
     */
    public function index(Request $request)
{
    try {
        $perPage = $request->per_page ?? 20;
        $page = $request->page ?? 1;

        // 👇 Añadido 'currency'
        $query = DepositRequest::with(['user', 'via', 'approvedBy', 'currency']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->currency_id) {
            $query->where('currency_id', $request->currency_id);
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->date_from) {
            $query->where('created_at', '>=', $request->date_from . ' 00:00:00');
        }

        if ($request->date_to) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        if ($request->search) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('id', 'like', '%' . $request->search . '%');
            });
        }

        $total = $query->count();

        $deposits = $query
            ->orderBy('created_at', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $stats = [
            'pending_count'   => DepositRequest::where('status', 'pending')->count(),
            'pending_amount'  => DepositRequest::where('status', 'pending')->sum('amount'),
            'approved_today'  => DepositRequest::where('status', 'approved')
                                    ->whereDate('approved_at', today())->count(),
            'completed_today' => DepositRequest::where('status', 'completed')
                                    ->whereDate('completed_at', today())->count(),
            'rejected_today'  => DepositRequest::where('status', 'rejected')
                                    ->whereDate('updated_at', today())->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'deposits'   => DepositRequestResource::collection($deposits),
                'stats'      => $stats,
                'pagination' => [
                    'total'        => $total,
                    'per_page'     => $perPage,
                    'current_page' => $page,
                    'last_page'    => ceil($total / $perPage),
                ],
            ],
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error'   => 'Error: ' . $e->getMessage(),
        ], 500);
    }
}

    /**
     * 🔍 Ver detalle de una solicitud (Admin)
     * GET /api/admin/deposits/{id}
     */
    public function show($id)
    {
        try {
            $deposit = DepositRequest::with(['user', 'via', 'approvedBy'])->find($id);

            if (!$deposit) {
                return response()->json(['error' => 'Solicitud no encontrada'], 404);
            }

            $balance = DB::table('accounts')
                ->where('user_id', $deposit->user_id)
                ->where('currency_id', $deposit->currency_id)
                ->sum('amount');

            $promo = DB::table('accounts')
                ->where('user_id', $deposit->user_id)
                ->where('currency_id', $deposit->currency_id)
                ->sum('credit_promotion');

            return response()->json([
                'success' => true,
                'data' => [
                    'deposit' => $deposit,
                    'user_balance' => round($balance ?? 0, 2),
                    'user_promo' => round($promo ?? 0, 2),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ Aprobar solicitud de depósito (Admin)
     * POST /api/admin/deposits/{id}/approve
     */
    public function approve(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'admin_id' => 'required|integer|exists:users,id',
            'admin_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $deposit = DepositRequest::find($id);

            if (!$deposit) {
                DB::rollBack();
                return response()->json(['error' => 'Solicitud no encontrada'], 404);
            }

            if ($deposit->status !== 'pending') {
                DB::rollBack();
                return response()->json([
                    'error' => 'Solo se pueden aprobar solicitudes pendientes',
                    'current_status' => $deposit->status
                ], 401);
            }

            $deposit->status = 'approved';
            $deposit->approved_by = $request->admin_id;
            $deposit->approved_at = now();
            $deposit->admin_notes = $request->admin_notes;
            $deposit->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud aprobada',
                'deposit' => $deposit,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al aprobar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ❌ Rechazar solicitud de depósito (Admin)
     * POST /api/admin/deposits/{id}/reject
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'admin_id' => 'required|integer|exists:users,id',
            'admin_notes' => 'required|string|min:5',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $deposit = DepositRequest::find($id);

            if (!$deposit) {
                DB::rollBack();
                return response()->json(['error' => 'Solicitud no encontrada'], 404);
            }

            if ($deposit->status !== 'pending') {
                DB::rollBack();
                return response()->json([
                    'error' => 'Solo se pueden rechazar solicitudes pendientes',
                    'current_status' => $deposit->status
                ], 401);
            }

            $deposit->status = 'rejected';
            $deposit->admin_notes = $request->admin_notes;
            $deposit->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud rechazada',
                'deposit' => $deposit,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al rechazar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 💰 Completar depósito (acredita al balance del usuario)
     * POST /api/admin/deposits/{id}/complete
     */
    public function complete(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'admin_id' => 'required|integer|exists:users,id',
            'admin_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $deposit = DepositRequest::find($id);

            if (!$deposit) {
                DB::rollBack();
                return response()->json(['error' => 'Solicitud no encontrada'], 404);
            }

            if ($deposit->status !== 'approved') {
                DB::rollBack();
                return response()->json([
                    'error' => 'Solo se pueden completar solicitudes aprobadas',
                    'current_status' => $deposit->status
                ], 401);
            }

            // Obtener balance actual
            $balance = DB::table('accounts')
                ->where('user_id', $deposit->user_id)
                ->where('currency_id', $deposit->currency_id)
                ->sum('amount');

            $promo = DB::table('accounts')
                ->where('user_id', $deposit->user_id)
                ->where('currency_id', $deposit->currency_id)
                ->sum('credit_promotion');

            $depositVia = Via::where('code', 'deposit')->first();

            // Crear transacción
            $account = new Account();
            $account->user_id = $deposit->user_id;
            $account->currency_id = $deposit->currency_id;
            $account->amount = $deposit->amount;
            $account->credit_promotion = 0;
            $account->credit = 0;
            $account->deposit = $deposit->amount;
            $account->withdrawal = 0;
            $account->via_id = $depositVia->id ?? $deposit->via_id;
            $account->description = 'Depósito aprobado #' . $deposit->id;
            $account->reference_type = 'deposit';
            $account->reference_id = $deposit->id;
            $account->balance_after = $balance + $deposit->amount;
            $account->promo_after = $promo;
            $account->save();

            $deposit->status = 'completed';
            $deposit->completed_at = now();
            $deposit->admin_notes = $request->admin_notes ?? $deposit->admin_notes;
            $deposit->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Depósito completado y acreditado',
                'deposit' => $deposit,
                'new_balance' => $account->balance_after,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al completar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ➕ Crear depósito manual (Admin)
     * POST /api/admin/deposits/manual
     */
    public function createManual(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'admin_id' => 'required|integer|exists:users,id',
            'currency_id' => 'required|string|max:10',
            'amount' => 'required|numeric|min:0.01',
            'via_id' => 'required|integer|exists:vias,id',
            'reference' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'admin_notes' => 'nullable|string',
            'add_promotion' => 'boolean',
            'promotion_amount' => 'nullable|numeric|min:0',
            'expires_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $balance = DB::table('accounts')
                ->where('user_id', $request->user_id)
                ->where('currency_id', $request->currency_id)
                ->sum('amount');

            $promo = DB::table('accounts')
                ->where('user_id', $request->user_id)
                ->where('currency_id', $request->currency_id)
                ->sum('credit_promotion');

            $promotionAmount = $request->add_promotion ? ($request->promotion_amount ?? 0) : 0;

            // Crear transacción de depósito
            $account = new Account();
            $account->user_id = $request->user_id;
            $account->currency_id = $request->currency_id;
            $account->amount = $request->amount;
            $account->credit_promotion = $promotionAmount;
            $account->credit = 0;
            $account->deposit = $request->amount;
            $account->withdrawal = 0;
            $account->via_id = $request->via_id;
            $account->description = $request->description ?? 'Depósito manual';
            $account->comments = $request->reference;
            $account->reference_type = 'manual_deposit';
            $account->reference_id = $request->admin_id;
            $account->expires_at = $request->expires_at;
            $account->balance_after = $balance + $request->amount;
            $account->promo_after = $promo + $promotionAmount;
            $account->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Depósito creado exitosamente',
                'account' => $account,
                'new_balance' => $account->balance_after,
                'new_promo' => $account->promo_after,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al crear el depósito: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📊 Estadísticas de depósitos
     * GET /api/admin/deposits/stats
     */
    public function stats(Request $request)
    {
        try {
            $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
            $dateTo = $request->date_to ?? now()->toDateString();

            $query = DepositRequest::whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

            $stats = [
                'total_requests' => $query->count(),
                'total_amount' => round($query->sum('amount'), 2),
                'by_status' => [
                    'pending' => (clone $query)->where('status', 'pending')->count(),
                    'approved' => (clone $query)->where('status', 'approved')->count(),
                    'rejected' => (clone $query)->where('status', 'rejected')->count(),
                    'completed' => (clone $query)->where('status', 'completed')->count(),
                    'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
                ],
                'by_currency' => DB::table('deposit_requests')
                    ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                    ->select('currency_id', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
                    ->groupBy('currency_id')
                    ->get(),
                'daily' => DB::table('deposit_requests')
                    ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                    ->select(
                        DB::raw('DATE(created_at) as date'),
                        DB::raw('COUNT(*) as count'),
                        DB::raw('SUM(amount) as total')
                    )
                    ->groupBy('date')
                    ->orderBy('date', 'asc')
                    ->get(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'range' => ['from' => $dateFrom, 'to' => $dateTo]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📥 Exportar
     * GET /api/admin/deposits/export
     */
    public function export(Request $request)
    {
        try {
            $query = DepositRequest::with(['user', 'via']);

            if ($request->status) $query->where('status', $request->status);
            if ($request->currency_id) $query->where('currency_id', $request->currency_id);
            if ($request->date_from) $query->where('created_at', '>=', $request->date_from . ' 00:00:00');
            if ($request->date_to) $query->where('created_at', '<=', $request->date_to . ' 23:59:59');

            $deposits = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $deposits,
                'generated_at' => now()->toDateTimeString(),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}