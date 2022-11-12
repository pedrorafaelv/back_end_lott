<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCheckCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('check_cards', function (Blueprint $table) {
            $table->id();
            $table->integer('card_id');
            $table->integer('raffle_id');
            $table->string('linea', 50);
            $table->integer('nro_faltas');
            $table->string('faltantes', 50);
            $table->string('combinacion', 50);
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
        Schema::dropIfExists('check_cards');
    }
}
