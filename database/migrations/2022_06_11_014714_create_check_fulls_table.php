<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCheckFullsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('check_fulls', function (Blueprint $table) {
            $table->id();
            $table->integer('card_id');
            $table->integer('raffle_id');
            $table->integer('nro_faltas');
            $table->string('faltantes', 50);
            $table->text('combinacion');
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
        Schema::dropIfExists('check_fulls');
    }
}
