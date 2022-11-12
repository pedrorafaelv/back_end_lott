<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFichaRaffleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ficha_raffle', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ficha_id')->nullable();
            $table->unsignedBigInteger('raffle_id')->nullable();
            $table->integer('indice')->nullable();
            $table->foreign('ficha_id')->references('id')->on('fichas')->onDelete('set null');
            $table->foreign('raffle_id')->references('id')->on('raffles')->onDelete('set null');
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
        Schema::dropIfExists('ficha_raffle');
    }
}
