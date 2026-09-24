<?php

namespace App\Models;

use App\Enums\WithdrawalAction;
use App\Enums\WithdrawalStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Withdrawal extends Model
{
    use HasFactory;

    /**
     * ⚠️ La tabla real se llama 'withdrawal_request', no 'withdrawals'
     */
    protected $table = 'withdrawal_request';

    protected $fillable = [
        'user_id',
        'account_id',
        'via_id',
        'amount',
        'commission',
        'net_amount',
        'currency_code',
        'status',
        'payment_data',
        'user_notes',
        'admin_notes',
        'rejection_reason',
        'transaction_reference',
        'approved_by',
        'rejected_by',
        'completed_by',
        'approved_at',
        'rejected_at',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'commission'   => 'decimal:2',
        'net_amount'   => 'decimal:2',
        // payment_data es TEXT, pero si guardas JSON, podemos auto-parsear
        'payment_data' => 'array',
        'status'       => WithdrawalStatus::class,
        'approved_at'  => 'datetime',
        'rejected_at'  => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /* ============================================
       RELACIONES
       ============================================ */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function via(): BelongsTo
    {
        return $this->belongsTo(Via::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function logs(): HasMany
    {
        // ⚠️ Ajusta 'withdrawal_id' si tu tabla de logs usa otro nombre
        return $this->hasMany(WithdrawalLog::class, 'withdrawal_id')
                    ->orderBy('created_at', 'desc');
    }

    /* ============================================
       SCOPES
       ============================================ */
    public function scopePending($query)
    {
        return $query->where('status', WithdrawalStatus::PENDING->value);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', WithdrawalStatus::APPROVED->value);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', WithdrawalStatus::COMPLETED->value);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', WithdrawalStatus::REJECTED->value);
    }

    /* ============================================
       HELPERS
       ============================================ */
    public function canBeApproved(): bool
    {
        return $this->status === WithdrawalStatus::PENDING;
    }

    public function canBeRejected(): bool
    {
        return in_array($this->status, [WithdrawalStatus::PENDING, WithdrawalStatus::APPROVED]);
    }

    public function canBeCompleted(): bool
    {
        return $this->status === WithdrawalStatus::APPROVED;
    }

    public function addLog(WithdrawalAction $action, ?int $adminId = null, ?string $notes = null, array $metadata = []): WithdrawalLog
    {
        return $this->logs()->create([
            'admin_id' => $adminId,
            'action'   => $action->value,
            'notes'    => $notes,
            'metadata' => $metadata,
        ]);
    }
}