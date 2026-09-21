<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('raffles', function (Blueprint $table) {
            // ⭐ Bandera: ¿se puede apostar con crédito promocional?
            $table->boolean('allow_promotional_bet')->default(false)
                  ->after('reward_full');
        });
    }

    public function down()
    {
        Schema::table('raffles', function (Blueprint $table) {
            $table->dropColumn('allow_promotional_bet');
        });
    }
};