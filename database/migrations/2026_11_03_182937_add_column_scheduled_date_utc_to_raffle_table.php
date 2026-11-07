<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnScheduledDateUtcToRaffleTable extends Migration
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
                $table->timestamp('scheduled_date_utc')->nullable();
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
            $table->dropColumn('scheduled_date_utc'); 
           
        });
    }
    
}
