<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            // Cambiar de int(11) a decimal(12,2)
            $table->decimal('deposit', 12, 2)->nullable()->change();
            $table->decimal('withdrawal', 12, 2)->nullable()->change();

            // Ampliar amount de decimal(8,2) a decimal(12,2)
            $table->decimal('amount', 12, 2)->nullable()->change();

            // Ampliar credit y credit_promotion para consistencia
            $table->decimal('credit', 12, 2)->nullable()->change();
            $table->decimal('credit_promotion', 12, 2)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->integer('deposit')->nullable()->change();
            $table->integer('withdrawal')->nullable()->change();
            $table->decimal('amount', 8, 2)->nullable()->change();
            $table->decimal('credit', 8, 2)->nullable()->change();
            $table->decimal('credit_promotion', 8, 2)->nullable()->change();
        });
    }
};