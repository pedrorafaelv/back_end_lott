<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');                                          //usuario
            $table->string('currency');                                                     //moneda
            $table->Decimal('amount', $precision = 8, $scale = 2)->nullable();              //monto actual
            $table->Decimal('credit', $precision = 8, $scale = 2)->nullable();              //credito
            $table->Decimal('credit_promotion', $precision = 8, $scale = 2)->nullable();    //creditos promocionales
            $table->integer('deposit')->nullable();                                         //deposito
            $table->integer('withdrawal')->nullable();                                      //retiro
            $table->integer('via')->nullable();                                             //via de retiro o deposito
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounts');
    }
}
