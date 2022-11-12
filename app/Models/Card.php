<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Ficha;
use App\Models\GroupFicha;
use App\Models\User;


class Card extends Model
{
    use HasFactory;


    public function Fichas(){

        return $this->belongsToMany(Ficha::class);    
    }

    public function GroupFichas(){

        return $this->belongsTo(GroupFicha::class);

    }

    public function Users(){

        return $this->belongsToMany(Card::class);
    }
     
}
