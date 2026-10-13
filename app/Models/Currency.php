<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


class Currency extends Model
{
    protected $table= 'currencies';

    protected $fillable = [
        'code', 'name', 'symbol', 'decimals', 'flag', 'is_crypto', 'is_active', 'display_order'
    ];

    // scopes 
     public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }
     public function scopeCrypto(Builder $q): Builder
    {
        return $q->where('is_crypto', true);
    }
     public function scopeOrdered(Builder $q): Builder
    {
        return $q->where('display_order', '!=', null);
    }
     public function scopeCanBeSeeByUser(Builder $q): Builder
    {
        return $q->where('display_order', 1);
    }
}