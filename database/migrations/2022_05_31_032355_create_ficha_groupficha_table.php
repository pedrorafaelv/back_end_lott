<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFichaGroupfichaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ficha_groupfichas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ficha_id')->nullable();
            $table->unsignedBigInteger('groupficha_id')->nullable();
            $table->foreign('groupficha_id')->references('id')->on('groupfichas')->onDelete('set null');
            $table->foreign('ficha_id')->references('id')->on('fichas')->onDelete('set null');
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
        Schema::dropIfExists('ficha_groupfichas');
    }
}
