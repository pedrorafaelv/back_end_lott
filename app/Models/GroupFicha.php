<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;   
use App\Models\Ficha;

class GroupFicha extends Model
{
    use HasFactory;

    protected $table = 'groupfichas';

    protected $fillable = [
        'name',
        'description',
        'status',
        'created_at',
    ];

    /**
     * The cards that belong to the GroupFicha
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cards()
    {
        return $this->hasMany(Card::class);
    }

    /**
     * The fichas that belong to the GroupFicha
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function fichas(): BelongsToMany
    {
        return $this->belongsToMany(Ficha::class, 'ficha_groupfichas', 'groupficha_id', 'ficha_id');
    }
}
