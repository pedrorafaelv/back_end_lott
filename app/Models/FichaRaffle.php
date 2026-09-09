<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FichaRaffle extends Model
{
    use HasFactory;

    protected $table = 'ficha_raffle';
    
    protected $fillable = [
        'raffle_id',
        'ficha_id',
        'indice',
        'created_at',
        'updated_at'
    ];
}
