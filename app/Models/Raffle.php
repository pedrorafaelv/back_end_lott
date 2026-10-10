<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Raffle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'user_id',
        'group_id',
        'groupficha_id',           
        'total_amount',
        'card_amount',
        'currency',
        'minimun_play',
        'maximun_play',
        'maximun_user_play',
        'retention_percent',
        'retention_amount',
        'admin_retention_percent',
        'admin_retention_amount',
        'raffle_type',
        'privacy',                 
        'reward_line',
        'percent_line',
        'reward_full',
        'percent_full',
        'admin_user',
        'scheduled_date',          
        'scheduled_hour',          
        'start_date',
        'end_date',
        'start_hour',
        'end_hour',
        'time_zone',
        'winner',
        'full_winner',
    ];

    protected $casts = [
        'start_date'              => 'datetime',
        'end_date'                => 'datetime',
        'scheduled_date'          => 'datetime',
        'total_amount'            => 'decimal:2',
        'card_amount'             => 'decimal:2',
        'retention_percent'       => 'integer',
        'retention_amount'        => 'integer',
        'admin_retention_percent' => 'integer',
        'admin_retention_amount'  => 'integer',
        'minimun_play'            => 'integer',
        'maximun_play'            => 'integer',
        'maximun_user_play'       => 'integer',
        'reward_line'             => 'integer',
        'percent_line'            => 'integer',
        'reward_full'             => 'integer',
        'percent_full'            => 'integer',
        'raffle_type'             => 'integer',
        'privacy'                 => 'integer',
    ];

    // ==================== Relaciones ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function cards(): BelongsToMany
    {
        return $this->belongsToMany(Card::class);
    }

    public function fichas(): BelongsToMany
    {
        return $this->belongsToMany(Ficha::class);
    }

    // ==================== Scopes ====================

    public function scopeActive($q)
    {
        return $q->where('start_date', '<=', now())
                 ->where('end_date', '>=', now());
    }

    public function scopeFinished($q)
    {
        return $q->where('end_date', '<', now());
    }

    public function scopeUpcoming($q)
    {
        return $q->where('start_date', '>', now());
    }
}