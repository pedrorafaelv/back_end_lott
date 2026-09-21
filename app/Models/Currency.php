<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'code', 'name', 'symbol', 'decimals', 'flag', 'is_crypto', 'is_active', 'display_order'
    ];
}