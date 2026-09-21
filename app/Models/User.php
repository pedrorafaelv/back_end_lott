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
    public function account(){

        return $this->hasOne(account::class);
    }
     public function Groups(){
          
         return $this->belongsToMany(Group::class);
    }     
    
    public function levels()
    {
        return $this->belongsToMany(Level::class);
    }
    
    public function Raffles(){
        
        return $this->hasMany(Raffle::class);
    }
    
    public function Cards(){

        return $this->belongsToMany(Card::class);
    }

    public function Roles(){

        return $this->belongsToMany(Role::class);
    }
}
