<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Currency;
use App\Models\Raffle;
use App\Models\Via;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    /**
     * 📊 ENDPOINT PRINCIPAL: Resumen completo del usuario
     * GET /api/account/summary?user_id=X
     */
    public function getSummary(Request $request)
    {
        try {
            $userId = $request->user_id;

            if (!$userId) {
                return response()->json(['error' =>true, 
                                        'message'=> 'user_id is required'], 400);
            }

            // 1. Obtener todas las monedas en las que el usuario tiene movimientos
            $currencies = DB::table('accounts')
                ->where('user_id', $userId)
                ->distinct()
                ->pluck('currency_code')
                ->toArray();

            // 2. Calcular balances por moneda
            $balances = [];
            foreach ($currencies as $currency) {
                $balances[$currency] = $this->calculateBalance($userId, $currency);
            }

            // 3. Totales agregados (para USD como referencia principal)
            $mainCurrency = 'USD';
            $totals = $this->calculateTotals($userId, $mainCurrency);

            // 4. Últimas 10 transacciones
            $recent = DB::table('accounts')
                ->leftJoin('vias', 'accounts.via_id', '=', 'vias.id')
                ->leftJoin('raffles', 'accounts.raffle_id', '=', 'raffles.id')
                ->where('accounts.user_id', $userId)
                ->orderBy('accounts.created_at', 'desc')
                ->limit(10)
                ->select(
                    'accounts.*',
                    'vias.code as via_code',
                    'vias.label as via_label',
                    'vias.icon as via_icon',
                    'vias.color as via_color',
                    'vias.type as via_type',
                    'raffles.name as raffle_name'
                )
                ->get();

            // 5. Solicitudes de retiro pendientes
            $withdrawalRequests = WithdrawalRequest::where('user_id', $userId)
                ->whereIn('status', ['pending', 'approved'])
                ->orderBy('created_at', 'desc')
                ->get();

            // 6. Monedas disponibles
            $availableCurrencies = Currency::where('is_active', true)
                ->orderBy('display_order')
                ->get();

            // 7. Vías disponibles
            $vias = Via::where('is_active', true)
                ->orderBy('display_order')
                ->get();

            return response()->json([
                'error'=>false, 
                'code'=>'001=>OK',
                'success' => true,
                'data' => [
                    'balances' => $balances,
                    'totals' => $totals,
                    'recent_transactions' => $recent,
                    'withdrawal_requests' => $withdrawalRequests,
                    'currencies' => $availableCurrencies,
                    'vias' => $vias,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error processing request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📋 ENDPOINT: Listado de transacciones con filtros y paginación
     * GET /api/account/transactions?user_id=X&currency=USD&page=1&per_page=20
     */
    public function getTransactions(Request $request)
    {
        try {
            $userId = $request->user_id;
            $currency = $request->currency_code;
            $perPage = $request->per_page ?? 20;
            $page = $request->page ?? 1;

            if (!$userId) {
                return response()->json(['error' => 'user_id is required'], 400);
            }

            $query = DB::table('accounts')
                ->leftJoin('vias', 'accounts.via_id', '=', 'vias.id')
                ->leftJoin('raffles', 'accounts.raffle_id', '=', 'raffles.id')
                ->where('accounts.user_id', $userId);

            // Filtro por moneda
            if ($currency) {
                $query->where('accounts.currency_code', $currency);
            }

            // Filtro por vía
            if ($request->via_id) {
                $query->where('accounts.via_id', $request->via_id);
            }

            // Filtro por tipo (credit/debit)
            if ($request->type) {
                $query->where('vias.type', $request->type);
            }

            // Filtro por fecha
            if ($request->date_from) {
                $query->where('accounts.created_at', '>=', $request->date_from . ' 00:00:00');
            }
            if ($request->date_to) {
                $query->where('accounts.created_at', '<=', $request->date_to . ' 23:59:59');
            }

            // Filtro por rango de monto
            if ($request->amount_min) {
                $query->where('accounts.amount', '>=', $request->amount_min);
            }
            if ($request->amount_max) {
                $query->where('accounts.amount', '<=', $request->amount_max);
            }

            $total = $query->count();

            $transactions = $query
                ->orderBy('accounts.created_at', 'desc')
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->select(
                    'accounts.*',
                    'vias.code as via_code',
                    'vias.label as via_label',
                    'vias.icon as via_icon',
                    'vias.color as via_color',
                    'vias.type as via_type',
                    'raffles.name as raffle_name'
                )
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'transactions' => $transactions,
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
     * 💰 ENDPOINT: Verificar si el usuario puede apostar
     * POST /api/account/checkBet
     */
    public function checkBet(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'raffle_id' => 'required|integer',
            'currency_code' => 'required|string',
        ]);

        $raffle = Raffle::find($request->raffle_id);
        if (!$raffle) {
            return response()->json(['error' => 'Raffle not found'], 404);
        }

        $balance = $this->getBalance($request->user_id, $request->currency_code);
        $promo = $this->getPromoBalance($request->user_id, $request->currency_code);

        $canUsePromo = $raffle->allow_promotional_bet;
        $totalAvailable = $canUsePromo ? $balance + $promo : $balance;

        if ($raffle->card_amount > $totalAvailable) {
            return response()->json([
                'can_bet' => false,
                'error' => 'Saldo insuficiente',
                'balance' => $balance,
                'promo' => $promo,
                'required' => $raffle->card_amount,
            ], 200);
        }

        // Calcular de dónde se descuenta
        $fromPromo = 0;
        $fromAmount = $raffle->card_amount;

        if ($canUsePromo && $promo >= $raffle->card_amount) {
            // Todo desde el promocional
            $fromPromo = $raffle->card_amount;
            $fromAmount = 0;
        }

        return response()->json([
            'can_bet' => true,
            'balance' => $balance,
            'promo' => $promo,
            'total_available' => $totalAvailable,
            'required' => $raffle->card_amount,
            'from_promo' => $fromPromo,
            'from_amount' => $fromAmount,
        ], 200);
    }

    /**
     * 🎲 ENDPOINT: Realizar una apuesta
     * POST /api/account/putBet
     */
    public function putBet(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'raffle_id' => 'required|integer',
            'currency_code' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $raffle = Raffle::find($request->raffle_id);
            if (!$raffle) {
                DB::rollBack();
                return response()->json(['error' => 'Raffle not found'], 404);
            }

            $balance = $this->getBalance($request->user_id, $request->currency_code);
            $promo = $this->getPromoBalance($request->user_id, $request->currency_code);
            $canUsePromo = $raffle->allow_promotional_bet;
            $totalAvailable = $canUsePromo ? $balance + $promo : $balance;

            if ($raffle->card_amount > $totalAvailable) {
                DB::rollBack();
                return response()->json([
                    'error' => 'Saldo insuficiente',
                    'balance' => $balance,
                    'promo' => $promo,
                    'required' => $raffle->card_amount,
                ], 401);
            }

            // Determinar de dónde se descuenta
            $fromPromo = 0;
            $fromAmount = 0;

            if ($canUsePromo && $promo >= $raffle->card_amount) {
                // Todo del promocional
                $fromPromo = $raffle->card_amount;
            } else {
                // Todo del real
                $fromAmount = $raffle->card_amount;
            }

            $betVia = Via::where('code', 'bet')->first();

            // Crear la transacción
            $account = new Account();
            $account->user_id = $request->user_id;
            $account->currency_code = $request->currency_code;
            $account->amount = -$fromAmount;
            $account->credit_promotion = -$fromPromo;
            $account->credit = 0;
            $account->deposit = 0;
            $account->withdrawal = $raffle->card_amount;
            $account->via_id = $betVia->id;
            $account->raffle_id = $raffle->id;
            $account->description = 'Apuesta: ' . $raffle->name;
            $account->comments = null;
            $account->balance_after = $balance - $fromAmount;
            $account->promo_after = $promo - $fromPromo;
            $account->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Apuesta registrada',
                'account' => $account,
                'from_promo' => $fromPromo,
                'from_amount' => $fromAmount,
                'new_balance' => $account->balance_after,
                'new_promo' => $account->promo_after,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al procesar la apuesta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🏆 ENDPOINT: Registrar un premio
     * POST /api/account/putAward
     */
    public function putAward(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'raffle_id' => 'required|integer',
            'currency_code' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:line,full',  // Tipo de premio
        ]);

        DB::beginTransaction();
        try {
            $raffle = Raffle::find($request->raffle_id);
            if (!$raffle) {
                DB::rollBack();
                return response()->json(['error' => 'Raffle not found'], 404);
            }

            // Verificar que no se haya pagado antes este premio específico
            $exists = Account::where('raffle_id', $raffle->id)
                ->where('user_id', $request->user_id)
                ->where('reference_type', 'award_' . $request->type)
                ->exists();

            if ($exists) {
                DB::rollBack();
                return response()->json(['error' => 'Este premio ya fue cobrado'], 401);
            }

            $balance = $this->getBalance($request->user_id, $request->currency_code);
            $promo = $this->getPromoBalance($request->user_id, $request->currency_code);
            $awardVia = Via::where('code', 'award')->first();

            $account = new Account();
            $account->user_id = $request->user_id;
            $account->currency_code = $request->currency_code;
            $account->amount = $request->amount;
            $account->credit_promotion = 0;
            $account->credit = 0;
            $account->deposit = $request->amount;
            $account->withdrawal = 0;
            $account->via_id = $awardVia->id;
            $account->raffle_id = $raffle->id;
            $account->description = 'Premio ' . ($request->type === 'line' ? 'de línea' : 'de cartón lleno') . ': ' . $raffle->name;
            $account->reference_type = 'award_' . $request->type;
            $account->reference_id = $raffle->id;
            $account->balance_after = $balance + $request->amount;
            $account->promo_after = $promo;
            $account->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Premio registrado',
                'account' => $account,
                'new_balance' => $account->balance_after,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al registrar el premio: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📥 ENDPOINT: Registrar un depósito (desde pasarela externa)
     * POST /api/account/store
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'currency_code' => 'required|string',
            'amount' => 'required|numeric',
            'via_id' => 'required|integer|exists:vias,id',
            'description' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $balance = $this->getBalance($request->user_id, $request->currency_code);
            $promo = $this->getPromoBalance($request->user_id, $request->currency_code);

            $account = new Account();
            $account->user_id = $request->user_id;
            $account->currency_code = $request->currency_code;
            $account->amount = $request->amount;
            $account->credit = $request->credit ?? 0;
            $account->credit_promotion = $request->credit_promotion ?? 0;
            $account->deposit = $request->deposit ?? $request->amount;
            $account->withdrawal = $request->withdrawal ?? 0;
            $account->via_id = $request->via_id;
            $account->raffle_id = $request->raffle_id;
            $account->description = $request->description;
            $account->comments = $request->comments;
            $account->reference_type = $request->reference_type;
            $account->reference_id = $request->reference_id;
            $account->expires_at = $request->expires_at;
            $account->balance_after = $balance + $request->amount;
            $account->promo_after = $promo + ($request->credit_promotion ?? 0);
            $account->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transacción registrada',
                'account' => $account,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al guardar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 💸 ENDPOINT: Solicitar un retiro
     * POST /api/account/requestWithdrawal
     */
    public function requestWithdrawal(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'currency_code' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'via_id' => 'required|integer|exists:vias,id',
            'payment_data' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $balance = $this->getBalance($request->user_id, $request->currency_code);

            if ($request->amount > $balance) {
                DB::rollBack();
                return response()->json([
                    'error' => 'Saldo insuficiente',
                    'balance' => $balance,
                ], 401);
            }

            // Obtener límites
            $limits = DB::table('transaction_limits')
                ->where('user_id', $request->user_id)
                ->where('currency_code', $request->currency_code)
                ->first();

            if ($limits) {
                // Verificar límite por transacción
                if ($request->amount > $limits->max_withdrawal_per_transaction) {
                    DB::rollBack();
                    return response()->json([
                        'error' => 'El monto excede el límite por transacción',
                        'max' => $limits->max_withdrawal_per_transaction,
                    ], 401);
                }

                // Verificar límite diario
                $todayTotal = DB::table('withdrawal_requests')
                    ->where('user_id', $request->user_id)
                    ->where('currency_code', $request->currency_code)
                    ->whereDate('created_at', today())
                    ->whereIn('status', ['pending', 'approved', 'completed'])
                    ->sum('amount');

                if (($todayTotal + $request->amount) > $limits->max_withdrawal_per_day) {
                    DB::rollBack();
                    return response()->json([
                        'error' => 'El monto excede el límite diario',
                        'daily_total' => $todayTotal,
                        'max' => $limits->max_withdrawal_per_day,
                    ], 401);
                }

                $commissionPercent = $limits->withdrawal_commission_percent;
                $commissionFixed = $limits->withdrawal_commission_fixed;
            } else {
                $commissionPercent = 3.00;
                $commissionFixed = 0;
            }

            $commission = ($request->amount * $commissionPercent / 100) + $commissionFixed;
            $netAmount = $request->amount - $commission;

            $withdrawal = new WithdrawalRequest();
            $withdrawal->user_id = $request->user_id;
            $withdrawal->currency_code = $request->currency_code;
            $withdrawal->amount = $request->amount;
            $withdrawal->commission = $commission;
            $withdrawal->net_amount = $netAmount;   
            $withdrawal->via_id = $request->via_id;
            $withdrawal->payment_data = json_encode($request->payment_data);
            $withdrawal->status = 'pending';
            $withdrawal->user_notes = $request->user_notes;
            $withdrawal->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de retiro creada. Espera la aprobación.',
                'withdrawal' => $withdrawal,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al procesar el retiro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ❌ ENDPOINT: Cancelar una solicitud de retiro pendiente
     * POST /api/account/cancelWithdrawal
     */
    public function cancelWithdrawal(Request $request)
    {
        $request->validate([
            'withdrawal_id' => 'required|integer',
            'user_id' => 'required|integer',
        ]);

        $withdrawal = WithdrawalRequest::find($request->withdrawal_id);

        if (!$withdrawal) {
            return response()->json(['error' => 'Solicitud no encontrada'], 404);
        }

        if ($withdrawal->user_id != $request->user_id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        if ($withdrawal->status !== 'pending') {
            return response()->json(['error' => 'Solo se pueden cancelar solicitudes pendientes'], 401);
        }

        $withdrawal->status = 'cancelled';
        $withdrawal->cancelled_at = now();
        $withdrawal->save();

        return response()->json([
            'success' => true,
            'message' => 'Solicitud cancelada',
        ], 200);
    }

    // ============================================
    // MÉTODOS PRIVADOS
    // ============================================

    private function calculateBalance($userId, $currency)
    {
        $amount = DB::table('accounts')
            ->where('user_id', $userId)
            ->where('currency_code', $currency)
            ->sum('amount');

        $promo = DB::table('accounts')
            ->where('user_id', $userId)
            ->where('currency_code', $currency)
            ->sum('credit_promotion');

        return [
            'currency_code' => $currency,
            'amount' => round($amount ?? 0, 2),
            'credit_promotion' => round($promo ?? 0, 2),
            'total' => round(($amount ?? 0) + ($promo ?? 0), 2),
        ];
    }

    private function calculateTotals($userId, $currency)
    {
        $totals = DB::table('accounts')
            ->leftJoin('vias', 'accounts.via_id', '=', 'vias.id')
            ->where('accounts.user_id', $userId)
            ->where('accounts.currency_code', $currency)
            ->select(
                DB::raw("SUM(CASE WHEN vias.code = 'deposit' THEN accounts.deposit ELSE 0 END) as deposits"),
                DB::raw("SUM(CASE WHEN vias.code = 'withdrawal' THEN accounts.withdrawal ELSE 0 END) as withdrawals"),
                DB::raw("SUM(CASE WHEN vias.code = 'bet' THEN accounts.withdrawal ELSE 0 END) as bets"),
                DB::raw("SUM(CASE WHEN vias.code = 'award' THEN accounts.amount ELSE 0 END) as awards"),
                DB::raw("SUM(CASE WHEN vias.code = 'promotion' THEN accounts.credit_promotion ELSE 0 END) as promotions")
            )
            ->first();

        return [
            'deposits' => round($totals->deposits ?? 0, 2),
            'withdrawals' => round($totals->withdrawals ?? 0, 2),
            'bets' => round($totals->bets ?? 0, 2),
            'awards' => round($totals->awards ?? 0, 2),
            'promotions' => round($totals->promotions ?? 0, 2),
        ];
    }

    private function getBalance($userId, $currency)
    {
        return DB::table('accounts')
            ->where('user_id', $userId)
            ->where('currency_code', $currency)
            ->sum('amount') ?? 0;
    }

    private function getPromoBalance($userId, $currency)
    {
        return DB::table('accounts')
            ->where('user_id', $userId)
            ->where('currency_code', $currency)
            ->sum('credit_promotion') ?? 0;
    }
}