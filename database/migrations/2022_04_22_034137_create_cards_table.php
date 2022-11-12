<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable();
            $table->integer('pos01')->nullable();
            $table->integer('pos02')->nullable();
            $table->integer('pos03')->nullable();
            $table->integer('pos04')->nullable();
            $table->integer('pos05')->nullable();
            $table->integer('pos06')->nullable();
            $table->integer('pos07')->nullable();
            $table->integer('pos08')->nullable();
            $table->integer('pos09')->nullable();
            $table->integer('pos10')->nullable();
            $table->integer('pos11')->nullable();
            $table->integer('pos12')->nullable();
            $table->integer('pos13')->nullable();
            $table->integer('pos14')->nullable();
            $table->integer('pos15')->nullable();
            $table->integer('pos16')->nullable();
            $table->integer('pos17')->nullable();
            $table->integer('pos18')->nullable();
            $table->integer('pos19')->nullable();
            $table->integer('pos20')->nullable();
            $table->integer('pos21')->nullable();
            $table->integer('pos22')->nullable();
            $table->integer('pos23')->nullable();
            $table->integer('pos24')->nullable();
            $table->integer('pos25')->nullable();
            $table->string('comb01', 255)->nullable();
            $table->string('comb02', 255)->nullable();
            $table->string('comb03', 255)->nullable();
            $table->string('comb04', 255)->nullable();
            $table->string('comb05', 255)->nullable();
            $table->string('comb06', 255)->nullable();
            $table->string('comb07', 255)->nullable();
            $table->string('comb08', 255)->nullable();
            $table->string('comb09', 255)->nullable();
            $table->string('comb10', 255)->nullable();
            $table->string('comb11', 255)->nullable();
            $table->string('comb12', 255)->nullable();
            $table->string('comb13', 255)->nullable();
            $table->string('comb14', 255)->nullable();
            $table->text('combTotal', 512)->nullable();
            $table->string('desc_pos01', 255)->nullable();
            $table->string('desc_pos02', 255)->nullable();
            $table->string('desc_pos03', 255)->nullable();
            $table->string('desc_pos04', 255)->nullable();
            $table->string('desc_pos05', 255)->nullable();
            $table->string('desc_pos06', 255)->nullable();
            $table->string('desc_pos07', 255)->nullable();
            $table->string('desc_pos08', 255)->nullable();
            $table->string('desc_pos09', 255)->nullable();
            $table->string('desc_pos10', 255)->nullable();
            $table->string('desc_pos11', 255)->nullable();
            $table->string('desc_pos12', 255)->nullable();
            $table->string('desc_pos13', 255)->nullable();
            $table->string('desc_pos14', 255)->nullable();
            $table->string('desc_pos15', 255)->nullable();
            $table->string('desc_pos16', 255)->nullable();
            $table->string('desc_pos17', 255)->nullable();
            $table->string('desc_pos18', 255)->nullable();
            $table->string('desc_pos19', 255)->nullable();
            $table->string('desc_pos20', 255)->nullable();
            $table->string('desc_pos21', 255)->nullable();
            $table->string('desc_pos22', 255)->nullable();
            $table->string('desc_pos23', 255)->nullable();
            $table->string('desc_pos24', 255)->nullable();
            $table->string('desc_pos25', 255)->nullable();
            $table->text('desc_comb01', 512)->nullable();
            $table->text('desc_comb02', 512)->nullable();
            $table->text('desc_comb03', 512)->nullable();
            $table->text('desc_comb04', 512)->nullable();
            $table->text('desc_comb05', 512)->nullable();
            $table->text('desc_comb06', 512)->nullable();
            $table->text('desc_comb07', 512)->nullable();
            $table->text('desc_comb08', 512)->nullable();
            $table->text('desc_comb09', 512)->nullable();
            $table->text('desc_comb10', 512)->nullable();
            $table->text('desc_comb11', 512)->nullable();
            $table->text('desc_comb12', 512)->nullable();
            $table->text('desc_comb13', 512)->nullable();
            $table->text('desc_comb14', 512)->nullable();
            $table->longText('desc_combTotal')->nullable();
            $table->string('active', 2)->nullable();
            $table->unsignedBigInteger('groupFicha_id')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
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
        Schema::dropIfExists('cards');
    }
}
