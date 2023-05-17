<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class lineaPosicion extends Model
{
    use HasFactory;
    protected $fillable = [
        'posicion',
        'linea',
        'created_at',
        'updated_at'
    ];
}
