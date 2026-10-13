<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'currency_from',
        'currency_to',
        'rate',
        'value_date',
        'scraped_at',
        'source',
    ];

    protected $casts = [
        'rate'       => 'decimal:4',
        'value_date' => 'date',
        'scraped_at' => 'datetime',
    ];

    // ==================== Scopes ====================

    public function scopeUsdToVes($query)
    {
        return $query->where('currency_from', 'USD')
                     ->where('currency_to', 'VES');
    }

    public function scopeLatestFirst($query)
    {
        return $query->orderBy('value_date', 'desc');
    }

    // ==================== Helpers ====================

    public static function getLatestUsdRate(): ?self
    {
        return self::usdToVes()->latestFirst()->first();
    }

    public static function getRateForDate(string $date): ?self
    {
        return self::usdToVes()
            ->where('value_date', $date)
            ->first();
    }
}