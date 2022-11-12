<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Raffle;

class Group extends Model
{
    use HasFactory;

public function Users(){
    
    return $this->belongsToMany(User::class);
} 

public function Raffles(){

    return $this->hasMany(Raffle::class);
} 

}
