<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class LevelUser extends Pivot
{
    protected $table = 'level_user';

    protected $casts = [
        'is_current' => 'boolean',
        'assigned_at' => 'datetime',
        'total_prizes' => 'decimal:2',
    ];
}
