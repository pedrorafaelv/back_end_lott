<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardGroupfichaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('card_groupficha', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('card_id');
            $table->unsignedBigInteger('groupficha_id');
            $table->foreign('groupficha_id')->references('id')->on('groupfichas')->onDelete('cascade');
            $table->foreign('card_id')->references('id')->on('cards')->onDelete('cascade');
            $table->timestamps();

            // $table->foreignId('card_id')
            // ->nullable()
            // ->constrained('cards')
            // ->cascadeOnUpdate()
            // ->nullOnDelete();
            
            // $table->foreignId('groupficha_id')
            // ->nullable()
            // ->constrained('groupfichas')
            // ->cascadeOnUpdate()
            // ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('card_groupficha');
    }
}
