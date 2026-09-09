<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Account extends Model
{
    use HasFactory;
   
    public function User(){

        return $this->belongsTo(User::class);    
   }
   protected $fillable = [
        'user_id',
        'currency_code',
        'amount',
        'credit',
        'credit_promotion',
        'deposit',
        'withdrawal',
        'via',
        'description',
        'comments',
        'created_at',
        'updated_at'
    ];
   
}
