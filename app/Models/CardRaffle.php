<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardRaffle extends Model
{
    use HasFactory;

    protected $table = 'card_raffle';

    protected $fillable = [
        'raffle_id',
        'card_id',
        'user_id',
        'indice',
        'status',
        'cancelled_at',
        'cancelled_reason',
        'active',
        'start_date',
        'end_date',
        'created_at',
        'updated_at',
    ];

    // ✅ Casts para fechas
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function raffle()
    {
        return $this->belongsTo(Raffle::class);
    }

    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
