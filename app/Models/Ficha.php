<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Card;
use App\Models\GroupFicha;
use App\Models\Raffle;

class Ficha extends Model
{
    use HasFactory;

    public function Cards(){

        return $this->belongsToMany(Card::class);    
    }

    public function GroupFichas(){

        return $this->belongsToMany(GroupFicha::class, 'ficha_groupficha', 'ficha_id', 'groupficha_id');       
    }

    public function Raffles(){

        return $this->belongsToMany(Raffle::class);       
    }

}
