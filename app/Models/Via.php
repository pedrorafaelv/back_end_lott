<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Via extends Model
{

    use HasFactory;

    protected $table = 'vias';

    protected $fillable = [
        'code', 'label', 'icon', 'color', 'type', 'is_active', 'display_order',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'display_order' => 'integer',
    ];

    // ==================== Scopes ====================
    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeCredits(Builder $q): Builder
    {
        return $q->where('type', 'credit');
    }

    public function scopeDebits(Builder $q): Builder
    {
        return $q->where('type', 'debit');
    }

    public function scopeNeutral(Builder $q): Builder
    {
        return $q->where('type', 'neutral');
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->where('display_order', '!=', null);
    }
    
    public function scopeCanSeeByUser(Builder $q): Builder
    {
        return $q->where('display_order', 1);
    }


    // ==================== Helpers ====================
    public function isCredit(): bool  { return $this->type === 'credit'; }
    public function isDebit(): bool   { return $this->type === 'debit'; }
    public function isNeutral(): bool { return $this->type === 'neutral'; }


    /**
     * Determina el signo a aplicar según contexto.
     * Útil para vías "neutral" que necesitan direction.
     */
    public function signFor(string $direction = 'credit'): int
    {
        return match ($this->type) {
            'credit'  => 1,
            'debit'   => -1,
            'neutral' => $direction === 'credit' ? 1 : -1,
        };
    }
}