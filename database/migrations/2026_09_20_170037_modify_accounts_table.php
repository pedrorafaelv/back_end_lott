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
           if (Schema::hasColumn('accounts', 'via')) {
                 $table->dropColumn('via');
    }
        });

        Schema::table('accounts', function (Blueprint $table) {
            // Nuevas columnas
            if (!Schema::hasColumn('accounts', 'via_id')) {
                 $table->foreignId('via_id')->nullable()->after('withdrawal')->constrained('vias');
            }
            if (!Schema::hasColumn('accounts', 'raffle_id')) {
                $table->foreignId('raffle_id')->nullable()->after('via_id')->constrained('raffles');
            }
            if (!Schema::hasColumn('accounts', 'balance_after')) {
                $table->decimal('balance_after', 12, 2)->nullable()->after('amount');
            }
            if (!Schema::hasColumn('accounts', 'promo_after')) {
                $table->decimal('promo_after', 12, 2)->nullable()->after('balance_after');
            }
            if (!Schema::hasColumn('accounts', 'reference_type')) {
                $table->string('reference_type', 50)->nullable()->after('comments');
            }
            if (!Schema::hasColumn('accounts', 'reference_id')) {
                $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');
            }
            if (!Schema::hasColumn('accounts', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('credit_promotion');
            }

            // Índices para performance
            $table->index(['user_id', 'currency_id', 'created_at'], 'idx_user_currency_date');
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
            $table->integer('via_id')->nullable()->after('withdrawal');
        });
    }
};