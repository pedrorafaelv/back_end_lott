<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Ficha;
use App\Models\GroupFicha;
use App\Models\User;


class Card extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name', 'groupFicha_id', 'active', 'start_date', 'end_date',
    ];

    public function Fichas()
    {
        return $this->belongsToMany(Ficha::class, 'card_ficha')
                    ->withPivot('posicion')
                    ->withTimestamps();
    }

    public function GroupFicha()
    {
        return $this->belongsTo(GroupFicha::class, 'groupFicha_id');
    }

    public function Users()
    {
        return $this->belongsToMany(User::class, 'card_user')
                    ->withTimestamps();
    }

    public function Raffles()
    {
        return $this->belongsToMany(Raffle::class, 'card_raffle')
                    ->withPivot('user_id', 'indice', 'status', 'cancelled_at',
                                'cancelled_reason', 'active', 'start_date', 'end_date')
                    ->withTimestamps();
    }
     
}
