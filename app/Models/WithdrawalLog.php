<?php

namespace App\Models;

use App\Enums\WithdrawalAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithdrawalLog extends Model
{
    /**
     * ⚠️ Ajusta si tu tabla tiene otro nombre.
     * Por defecto Laravel usaría 'withdrawal_logs'.
     */
    protected $table = 'withdrawal_logs';

    protected $fillable = [
        'withdrawal_id',
        'admin_id',
        'action',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'action'   => WithdrawalAction::class,
        'metadata' => 'array',
    ];

    public function withdrawal(): BelongsTo
    {
        return $this->belongsTo(Withdrawal::class, 'withdrawal_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}