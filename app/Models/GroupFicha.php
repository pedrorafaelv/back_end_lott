<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Ficha;
use App\Models\User;
use App\Models\FichaGroupficha;



class Groupficha extends Model
{
    use HasFactory;
    /**
    * The cards that belong to the GroupFicha
    *
    * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
    */
    public function Cards(): BelongsToMany{
        return $this->hasMany(Card::class);
    }
    
    /**
    * The fichass that belong to the GroupFicha
    *
    * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
    */
    public function Fichas(): BelongsToMany{
        return $this->BelongsToMany(Ficha::class, 'ficha_groupfichas', 'groupficha_id', 'ficha_id');
    }

}
