<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
Use App\Models\Ficha;
Use App\Models\Card;
Use App\Models\User;
Use App\Models\Group;


class Raffle extends Model
{
    use HasFactory;

    public function Cards(){
        
        return $this->belongsToMany(Card::class);
    }

    public function Fichas(){
        
        return $this->belongsToMany(Ficha::class);
    }

    public function Users(){
        
        return $this->belongsTo(User::class);
    }
    
    public function Groups(){
        
        return $this->belongsTo(Group::class);
    }
}
