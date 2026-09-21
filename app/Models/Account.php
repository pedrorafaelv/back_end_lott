<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'currency_code',
        'amount',
        'balance_after',
        'promo_after',
        'credit',
        'credit_promotion',
        'expires_at',
        'deposit',
        'withdrawal',
        'via_id',
        'raffle_id',
        'description',
        'comments',
        'reference_type',
        'reference_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'promo_after' => 'decimal:2',
        'credit' => 'decimal:2',
        'credit_promotion' => 'decimal:2',
        'deposit' => 'decimal:2',
        'withdrawal' => 'decimal:2',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function via()
    {
        return $this->belongsTo(Via::class);
    }

    public function raffle()
    {
        return $this->belongsTo(Raffle::class);
    }
}