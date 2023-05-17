<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToRafflesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('raffles', function (Blueprint $table) {
            $table->after('group_id', function($table){ 
                $table->integer('groupficha_id')->nullable();
                $table->unsignedDecimal('total_amount', $precision = 8, $scale = 2)->nullable(); //total bet
                $table->unsignedDecimal('card_amount', $precision = 8, $scale = 2)->nullable(); //bet bike
                $table->integer('minimun_play')->nullable();
                $table->integer('maximun_play')->nullable();
                $table->integer('maximun_user_play')->nullable();
                $table->integer('retention_percent')->nullable();
                $table->integer('retention_amount')->nullable();
                $table->integer('admin_retention_percent')->nullable();
                $table->integer('admin_retention_amount')->nullable();
                $table->integer('raffle_type')->nullable();                // manual-automatic
                $table->integer('privacy')->nullable();                    //public- private
                $table->integer('reward_line')->nullable();                //yes - no
                $table->integer('percent_line')->nullable();               //percent for line reward
                $table->integer('reward_full')->nullable();                //yes-no
                $table->integer('percent_full')->nullable();               //percent for full reward
                $table->unsignedBigInteger('admin_user')->nullable();
                $table->timestamp('scheduled_date')->nullable();
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
            $table->dropColumn('groupficha_id'); 
            $table->dropColumn('total_amount'); 
            $table->dropColumn('card_amount');             
                $table->dropColumn('minimun_play');
                $table->dropColumn('maximun_play');
                $table->dropColumn('maximun_user_play');
                $table->dropColumn('retention_percent');
                $table->dropColumn('retention_amount');
                $table->dropColumn('admin_retention_percent');
                $table->dropColumn('admin_retention_amount');
                $table->dropColumn('raffle_type');              //manual-automatic
                $table->dropColumn('privacy');                  //public- private 
                $table->dropColumn('reward_line');              //yes-no
                $table->dropColumn('percent_line');             //percent for line reward
                $table->dropColumn('reward_full');              //yes-no
                $table->dropColumn('percent_full');             //percent for full reward
                $table->dropColumn('admin_user');
                $table->dropColumn('scheduled_date');
         
        });
    }
}
