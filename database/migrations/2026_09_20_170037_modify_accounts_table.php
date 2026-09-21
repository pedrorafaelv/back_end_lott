<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            // ⭐ Cambiar via (string numérico) a via_id (FK)
            $table->dropColumn('via');
        });

        Schema::table('accounts', function (Blueprint $table) {
            // Nuevas columnas
            $table->foreignId('via_id')->nullable()->after('withdrawal')->constrained('vias');
            $table->foreignId('raffle_id')->nullable()->after('via_id')->constrained('raffles');
            $table->decimal('balance_after', 12, 2)->nullable()->after('amount');
            $table->decimal('promo_after', 12, 2)->nullable()->after('balance_after');
            $table->string('reference_type', 50)->nullable()->after('comments');
            $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');
            $table->timestamp('expires_at')->nullable()->after('credit_promotion');

            // Índices para performance
            $table->index(['user_id', 'currency_code', 'created_at'], 'idx_user_currency_date');
            $table->index(['via_id', 'created_at'], 'idx_via_date');
            $table->index(['raffle_id'], 'idx_raffle');
        });
    }

    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropForeign(['via_id']);
            $table->dropForeign(['raffle_id']);
            $table->dropColumn([
                'via_id', 'raffle_id', 'balance_after', 'promo_after',
                'reference_type', 'reference_id', 'expires_at'
            ]);
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->integer('via')->nullable()->after('withdrawal');
        });
    }
};