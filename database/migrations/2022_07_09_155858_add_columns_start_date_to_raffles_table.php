<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsStartDateToRafflesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('raffles', function (Blueprint $table) {
            $table->after('scheduled_date', function($table){     
                $table->time('scheduled_hour')->nullable();         
                $table->time('start_hour')->nullable();
                $table->time('end_hour')->nullable();
                $table->string('time_zone')->nullable();
                $table->string('winner')->nullable();
                $table->string('full_winner')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('raffles', function (Blueprint $table) {
            $table->dropColumn('scheduled_hour'); 
            $table->dropColumn('start_hour'); 
            $table->dropColumn('end_hour'); 
            $table->dropColumn('time_zone');   
            $table->dropColumn('winner');   
            $table->dropColumn('full_winner');   
        });
    }
}
