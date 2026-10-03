<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Account;
use App\Models\Card;
use App\Models\Group;
use App\Models\Level;
use App\Models\Raffle;
use App\Models\Role;
use App\Models\WithdrawalRequest;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'last_name',
        'birth_date',
        'document',
        'gender',
        'phone',
        'phone_verified_at',
        'country',
        'state',
        'city',
        'address',
        'role',
        'firebase_localId',
        'firebase_token',
        'firebase_last_conection',
        'is_admin',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin'=>'boolean',
    ];
    public function accounts(){

        return $this->hasMany(account::class);
    }
     public function groups(){
          
         return $this->belongsToMany(Group::class);
    }     
    
    public function raffles(){
        
        return $this->hasMany(Raffle::class);
    }
    
    public function cards(){

        return $this->belongsToMany(Card::class);
    }

    public function roles(){

        return $this->belongsToMany(Role::class);
    }
     

    public function levels() {
    return $this->belongsToMany(Level::class, 'level_user')
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
    /**
 * Nivel actual del usuario
 */
    public function currentLevel()
        {
          return $this->belongsToMany(Level::class, 'level_user')
                ->wherePivot('is_current', true)
                ->withPivot([
                    'games_played', 'wins', 'active_raffles',
                    'total_raffles', 'total_prizes',
                    'is_current', 'assigned_at', 'notes',
                ])
                ->withTimestamps();
}

/**
 * Acceso directo al nivel activo (con fallback a Novato si no tiene)
 */
public function getActiveLevelAttribute()
{
     $level = $this->levels()
        ->wherePivot('is_current', true)
        ->first();

    // Fallback: si no tiene nivel activo, devuelve Novato
    if (!$level) {
        $level = Level::where('slug', 'novato')->first();
    }
    return $level;
}

public function withdrawals()
{
    return $this->hasMany(Withdrawal::class, 'user_id');
}

public function approvedWithdrawals()
{
    return $this->hasMany(Withdrawal::class, 'approved_by');
}

public function rejectedWithdrawals()
{
    return $this->hasMany(Withdrawal::class, 'rejected_by');
}

public function completedWithdrawals()
{
    return $this->hasMany(Withdrawal::class, 'completed_by');
}
}
