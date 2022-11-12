<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckFull extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_id',
        'raffle_id',
        'nro_faltas',
        'faltantes',
        'combinacion',
        'created_at',
        'updated_at'
    ];
}
