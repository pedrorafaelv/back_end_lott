<?php

namespace App\Services;

use App\Enums\WithdrawalAction;
use App\Enums\WithdrawalStatus;
use App\Models\Account;
use App\Models\Withdrawal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WithdrawalService
{
    /* ============================================
       LISTADO + FILTROS
       ============================================ */
    public function list(array $filters): LengthAwarePaginator
    {
        $query = Withdrawal::with(['user:id,name,last_name,email', 'via', 'account'])
            ->orderBy('created_at', 'desc');

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['currency_code'])) {
            $query->where('currency_code', $filters['currency_code']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) ($filters['per_page'] ?? 20), 100);

        return $query->paginate($perPage);
    }

    /* ============================================
       ESTADÍSTICAS DASHBOARD
       ============================================ */
    public function dashboardStats(): array
    {
        $today = now()->startOfDay();

        return [
            'pending_count'   => Withdrawal::pending()->count(),
            'pending_amount'  => (float) Withdrawal::pending()->sum('amount'),
            'approved_today'  => Withdrawal::approved()->where('approved_at', '>=', $today)->count(),
            'completed_today' => Withdrawal::completed()->where('completed_at', '>=', $today)->count(),
            'rejected_today'  => Withdrawal::rejected()->where('rejected_at', '>=', $today)->count(),
        ];
    }

    /* ============================================
       ESTADÍSTICAS DETALLADAS
       ============================================ */
    public function detailedStats(array $filters = []): array
    {
        $query = Withdrawal::query();

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $byStatus = (clone $query)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $byCurrency = (clone $query)
            ->select('currency_code', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
            ->groupBy('currency_code')
            ->get()
            ->toArray();

        return [
            'total_requests'   => (clone $query)->count(),
            'total_amount'     => (float) (clone $query)->sum('amount'),
            'total_commission' => (float) (clone $query)->sum('commission'),
            'total_net'        => (float) (clone $query)->sum('net_amount'),
            'by_status'        => $byStatus,
            'by_currency'      => $byCurrency,
        ];
    }

    /* ============================================
       APROBAR
       ============================================ */
    public function approve(Withdrawal $withdrawal, int $adminId, ?string $notes = null): Withdrawal
    {
        if (!$withdrawal->canBeApproved()) {
            throw ValidationException::withMessages([
                'status' => "No se puede aprobar un retiro en estado '{$withdrawal->status->value}'.",
            ]);
        }

        return DB::transaction(function () use ($withdrawal, $adminId, $notes) {
            $withdrawal->update([
                'status'      => WithdrawalStatus::APPROVED->value,
                'admin_notes' => $notes,
                'approved_by' => $adminId,
                'approved_at' => now(),
            ]);

            $withdrawal->addLog(WithdrawalAction::APPROVED, $adminId, $notes);

            return $withdrawal->fresh(['user', 'via', 'account']);
        });
    }

    /* ============================================
       RECHAZAR + REEMBOLSO
       ============================================ */
    public function reject(Withdrawal $withdrawal, int $adminId, string $reason, ?string $notes = null): Withdrawal
    {
        if (!$withdrawal->canBeRejected()) {
            throw ValidationException::withMessages([
                'status' => "No se puede rechazar un retiro en estado '{$withdrawal->status->value}'.",
            ]);
        }

        return DB::transaction(function () use ($withdrawal, $adminId, $reason, $notes) {
            // Reembolsar al usuario
            $account = Account::where('user_id', $withdrawal->user_id)
                ->where('currency_code', $withdrawal->currency_code)
                ->first();

            if ($account) {
                $account->increment('amount', $withdrawal->amount);
            }

            $withdrawal->update([
                'status'           => WithdrawalStatus::REJECTED->value,
                'rejection_reason' => $reason,
                'admin_notes'      => $notes,
                'rejected_by'      => $adminId,
                'rejected_at'      => now(),
            ]);

            $withdrawal->addLog(
                WithdrawalAction::REJECTED,
                $adminId,
                $reason,
                ['refunded' => true, 'refund_amount' => (float) $withdrawal->amount]
            );

            return $withdrawal->fresh(['user', 'via', 'account']);
        });
    }

    /* ============================================
       COMPLETAR
       ============================================ */
    public function complete(Withdrawal $withdrawal, int $adminId, string $reference, ?string $notes = null): Withdrawal
    {
        if (!$withdrawal->canBeCompleted()) {
            throw ValidationException::withMessages([
                'status' => 'Solo se pueden completar retiros aprobados.',
            ]);
        }

        return DB::transaction(function () use ($withdrawal, $adminId, $reference, $notes) {
            $withdrawal->update([
                'status'                => WithdrawalStatus::COMPLETED->value,
                'transaction_reference' => $reference,
                'admin_notes'           => $notes,
                'completed_by'          => $adminId,
                'completed_at'          => now(),
            ]);

            $withdrawal->addLog(
                WithdrawalAction::COMPLETED,
                $adminId,
                $notes,
                ['transaction_reference' => $reference]
            );

            return $withdrawal->fresh(['user', 'via', 'account']);
        });
    }

    /* ============================================
       EXPORTAR
       ============================================ */
    public function export(array $filters): array
    {
        $query = Withdrawal::with(['user:id,name,last_name,email', 'via']);

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['currency_code'])) {
            $query->where('currency_code', $filters['currency_code']);
        }
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->limit(5000)->get()->toArray();
    }
}