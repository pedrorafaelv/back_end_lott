<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\User;
use App\Models\Via;
use App\Models\WithdrawalLog;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WithdrawalController extends Controller
{
    /**
     * 📋 ENDPOINT: Listar todas las solicitudes de retiro (Admin)
     * GET /api/admin/withdrawals?status=pending&page=1&per_page=20
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->per_page ?? 20;
            $page = $request->page ?? 1;

            $query = WithdrawalRequest::with(['user', 'via', 'approvedBy']);

            // Filtros
            if ($request->status) {
                $query->where('status', $request->status);
            }

            if ($request->currency_code) {
                $query->where('currency_code', $request->currency_code);
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
                      ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            }

            $total = $query->count();

            $withdrawals = $query
                ->orderBy('created_at', 'desc')
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            // Estadísticas
            $stats = [
                'pending_count' => WithdrawalRequest::where('status', 'pending')->count(),
                'pending_amount' => WithdrawalRequest::where('status', 'pending')->sum('amount'),
                'approved_today' => WithdrawalRequest::where('status', 'approved')
                    ->whereDate('approved_at', today())->count(),
                'completed_today' => WithdrawalRequest::where('status', 'completed')
                    ->whereDate('completed_at', today())->count(),
                'rejected_today' => WithdrawalRequest::where('status', 'rejected')
                    ->whereDate('updated_at', today())->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'withdrawals' => $withdrawals,
                    'stats' => $stats,
                    'pagination' => [
                        'total' => $total,
                        'per_page' => $perPage,
                        'current_page' => $page,
                        'last_page' => ceil($total / $perPage),
                    ]
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
     * 🔍 ENDPOINT: Ver detalle de una solicitud (Admin)
     * GET /api/admin/withdrawals/{id}
     */
    public function show($id)
    {
        try {
            $withdrawal = WithdrawalRequest::with(['user', 'via', 'approvedBy', 'logs.admin'])
                ->find($id);

            if (!$withdrawal) {
                return response()->json(['error' => 'Solicitud no encontrada'], 404);
            }

            // Balance actual del usuario
            $balance = DB::table('accounts')
                ->where('user_id', $withdrawal->user_id)
                ->where('currency_code', $withdrawal->currency_code)
                ->sum('amount');

            return response()->json([
                'success' => true,
                'data' => [
                    'withdrawal' => $withdrawal,
                    'user_balance' => round($balance ?? 0, 2),
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
     * ✅ ENDPOINT: Aprobar una solicitud de retiro (Admin)
     * POST /api/admin/withdrawals/{id}/approve
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
            $withdrawal = WithdrawalRequest::find($id);

            if (!$withdrawal) {
                DB::rollBack();
                return response()->json(['error' => 'Solicitud no encontrada'], 404);
            }

            if ($withdrawal->status !== 'pending') {
                DB::rollBack();
                return response()->json([
                    'error' => 'Solo se pueden aprobar solicitudes pendientes',
                    'current_status' => $withdrawal->status
                ], 401);
            }

            // Verificar que el usuario aún tenga saldo suficiente
            $balance = DB::table('accounts')
                ->where('user_id', $withdrawal->user_id)
                ->where('currency_code', $withdrawal->currency_code)
                ->sum('amount');

            if ($balance < $withdrawal->amount) {
                DB::rollBack();
                return response()->json([
                    'error' => 'El usuario ya no tiene saldo suficiente',
                    'balance' => round($balance ?? 0, 2),
                    'required' => $withdrawal->amount,
                ], 401);
            }

            // Actualizar estado
            $withdrawal->status = 'approved';
            $withdrawal->approved_by = $request->admin_id;
            $withdrawal->approved_at = now();
            $withdrawal->admin_notes = $request->admin_notes;
            $withdrawal->save();

            // Log
            WithdrawalLog::create([
                'withdrawal_request_id' => $withdrawal->id,
                'admin_id' => $request->admin_id,
                'action' => 'approved',
                'notes' => $request->admin_notes,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud aprobada',
                'withdrawal' => $withdrawal,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al aprobar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ❌ ENDPOINT: Rechazar una solicitud de retiro (Admin)
     * POST /api/admin/withdrawals/{id}/reject
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
            $withdrawal = WithdrawalRequest::find($id);

            if (!$withdrawal) {
                DB::rollBack();
                return response()->json(['error' => 'Solicitud no encontrada'], 404);
            }

            if ($withdrawal->status !== 'pending') {
                DB::rollBack();
                return response()->json([
                    'error' => 'Solo se pueden rechazar solicitudes pendientes',
                    'current_status' => $withdrawal->status
                ], 401);
            }

            $withdrawal->status = 'rejected';
            $withdrawal->admin_notes = $request->admin_notes;
            $withdrawal->save();

            // Log
            WithdrawalLog::create([
                'withdrawal_request_id' => $withdrawal->id,
                'admin_id' => $request->admin_id,
                'action' => 'rejected',
                'notes' => $request->admin_notes,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud rechazada',
                'withdrawal' => $withdrawal,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al rechazar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 💰 ENDPOINT: Marcar como completado (pago realizado) (Admin)
     * POST /api/admin/withdrawals/{id}/complete
     */
    public function complete(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'admin_id' => 'required|integer|exists:users,id',
            'transaction_reference' => 'required|string|min:3',  // ID de la transferencia bancaria
            'admin_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $withdrawal = WithdrawalRequest::find($id);

            if (!$withdrawal) {
                DB::rollBack();
                return response()->json(['error' => 'Solicitud no encontrada'], 404);
            }

            if ($withdrawal->status !== 'approved') {
                DB::rollBack();
                return response()->json([
                    'error' => 'Solo se pueden completar solicitudes aprobadas',
                    'current_status' => $withdrawal->status
                ], 401);
            }

            // Descontar del balance del usuario
            $balance = DB::table('accounts')
                ->where('user_id', $withdrawal->user_id)
                ->where('currency_code', $withdrawal->currency_code)
                ->sum('amount');

            if ($balance < $withdrawal->amount) {
                DB::rollBack();
                return response()->json([
                    'error' => 'Saldo insuficiente para completar el retiro',
                    'balance' => round($balance ?? 0, 2),
                ], 401);
            }

            $promo = DB::table('accounts')
                ->where('user_id', $withdrawal->user_id)
                ->where('currency_code', $withdrawal->currency_code)
                ->sum('credit_promotion');

            $withdrawalVia = Via::where('code', 'withdrawal')->first();

            // Crear transacción de retiro (resta del balance)
            $account = new Account();
            $account->user_id = $withdrawal->user_id;
            $account->currency_code = $withdrawal->currency_code;
            $account->amount = -$withdrawal->amount;
            $account->credit_promotion = 0;
            $account->credit = 0;
            $account->deposit = 0;
            $account->withdrawal = $withdrawal->amount;
            $account->via_id = $withdrawalVia->id;
            $account->description = 'Retiro #' . $withdrawal->id;
            $account->reference_type = 'withdrawal';
            $account->reference_id = $withdrawal->id;
            $account->balance_after = $balance - $withdrawal->amount;
            $account->promo_after = $promo;
            $account->save();

            // Crear transacción de comisión (si aplica)
            if ($withdrawal->commission > 0) {
                $commissionVia = Via::where('code', 'commission')->first();

                $commissionAccount = new Account();
                $commissionAccount->user_id = $withdrawal->user_id;
                $commissionAccount->currency_code = $withdrawal->currency_code;
                $commissionAccount->amount = 0;
                $commissionAccount->credit_promotion = 0;
                $commissionAccount->credit = 0;
                $commissionAccount->deposit = 0;
                $commissionAccount->withdrawal = $withdrawal->commission;
                $commissionAccount->via_id = $commissionVia->id;
                $commissionAccount->description = 'Comisión retiro #' . $withdrawal->id;
                $commissionAccount->reference_type = 'withdrawal_commission';
                $commissionAccount->reference_id = $withdrawal->id;
                $commissionAccount->balance_after = $account->balance_after;
                $commissionAccount->promo_after = $promo;
                $commissionAccount->save();
            }

            // Actualizar solicitud
            $withdrawal->status = 'completed';
            $withdrawal->completed_at = now();
            $withdrawal->admin_notes = $request->admin_notes;
            $withdrawal->save();

            // Guardar referencia de la transacción
            $paymentData = $withdrawal->payment_data ?? [];
            $paymentData['transaction_reference'] = $request->transaction_reference;
            $paymentData['completed_at'] = now()->toDateTimeString();
            $withdrawal->payment_data = $paymentData;
            $withdrawal->save();

            // Log
            WithdrawalLog::create([
                'withdrawal_request_id' => $withdrawal->id,
                'admin_id' => $request->admin_id,
                'action' => 'completed',
                'notes' => $request->admin_notes,
                'data' => ['transaction_reference' => $request->transaction_reference],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Retiro completado',
                'withdrawal' => $withdrawal,
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
     * 📊 ENDPOINT: Estadísticas de retiros (Admin Dashboard)
     * GET /api/admin/withdrawals/stats
     */
    public function stats(Request $request)
    {
        try {
            $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
            $dateTo = $request->date_to ?? now()->toDateString();

            $query = WithdrawalRequest::whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

            $stats = [
                'total_requests' => $query->count(),
                'total_amount' => round($query->sum('amount'), 2),
                'total_commission' => round($query->sum('commission'), 2),
                'total_net' => round($query->sum('net_amount'), 2),
                'by_status' => [
                    'pending' => (clone $query)->where('status', 'pending')->count(),
                    'approved' => (clone $query)->where('status', 'approved')->count(),
                    'rejected' => (clone $query)->where('status', 'rejected')->count(),
                    'completed' => (clone $query)->where('status', 'completed')->count(),
                    'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
                ],
                'by_currency' => DB::table('withdrawal_requests')
                    ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                    ->select('currency_code', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
                    ->groupBy('currency_code')
                    ->get(),
                'daily' => DB::table('withdrawal_requests')
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
     * 📥 ENDPOINT: Exportar listado de retiros (Admin)
     * GET /api/admin/withdrawals/export
     */
    public function export(Request $request)
    {
        try {
            $query = WithdrawalRequest::with(['user', 'via']);

            if ($request->status) {
                $query->where('status', $request->status);
            }

            if ($request->currency_code) {
                $query->where('currency_code', $request->currency_code);
            }

            if ($request->date_from) {
                $query->where('created_at', '>=', $request->date_from . ' 00:00:00');
            }

            if ($request->date_to) {
                $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
            }

            $withdrawals = $query->orderBy('created_at', 'desc')->get();

            // Aquí puedes generar un PDF o Excel con maatwebsite/excel
            // Por ahora devolvemos el JSON para que el frontend lo exporte

            return response()->json([
                'success' => true,
                'data' => $withdrawals,
                'generated_at' => now()->toDateTimeString(),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📜 ENDPOINT: Historial de logs de una solicitud (Admin)
     * GET /api/admin/withdrawals/{id}/logs
     */
    public function logs($id)
    {
        try {
            $withdrawal = WithdrawalRequest::find($id);

            if (!$withdrawal) {
                return response()->json(['error' => 'Solicitud no encontrada'], 404);
            }

            $logs = WithdrawalLog::with('admin')
                ->where('withdrawal_request_id', $id)
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $logs,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📊 ENDPOINT: Resumen general del usuario (para el propio usuario)
     * GET /api/account/myWithdrawals?user_id=X
     */
    public function myWithdrawals(Request $request)
    {
        try {
            $userId = $request->user_id;

            if (!$userId) {
                return response()->json(['error' => 'user_id is required'], 400);
            }

            $withdrawals = WithdrawalRequest::with('via')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $withdrawals,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}