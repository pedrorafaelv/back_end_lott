<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'icon',
        'color',
        'active',
        'order',
        'max_raffles_active',
        'max_amount',
        'max_retention_percent',
        'can_create_private',
        'can_use_auto_type',
        'can_use_custom_fichas',
        'min_games_played',
        'min_days_registered',
        'min_wins',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'can_create_private' => 'boolean',
        'can_use_auto_type' => 'boolean',
        'can_use_custom_fichas' => 'boolean',
        'max_amount' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /* ============================================
       RELACIONES
       ============================================ */
    public function users()
    {
        return $this->belongsToMany(User::class, 'level_user')
                    ->withPivot([
                        'games_played',
                        'wins',
                        'active_raffles',
                        'total_raffles',
                        'total_prizes',
                        'is_current',
                        'assigned_at',
                        'notes',
                    ])
                    ->withTimestamps();
    }

    /* ============================================
       SCOPES
       ============================================ */
    public function scopeActive($query)
    {
        return $query->where('active', '1');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}