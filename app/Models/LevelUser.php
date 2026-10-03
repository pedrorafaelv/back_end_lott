<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class LevelUser extends Pivot
{
    protected $table = 'level_user';
     protected $fillable = [
        'level_id',
        'user_id',
        'games_played',
        'wins',
        'active_raffles',
        'total_raffles',
        'total_prizes',
        'is_current',
        'assigned_at',
        'note',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'assigned_at' => 'datetime',
        'total_prizes' => 'decimal:2',
    ];
}
