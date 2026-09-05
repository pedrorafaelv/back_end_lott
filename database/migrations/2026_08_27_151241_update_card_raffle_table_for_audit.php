<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateCardRaffleTableForAudit extends Migration
{
    public function up()
    {
        // Deshabilitar verificaciones
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 1. Verificar si la columna status ya existe
        if (!Schema::hasColumn('card_raffle', 'status')) {
            Schema::table('card_raffle', function (Blueprint $table) {
                $table->enum('status', ['active', 'cancelled', 'winner', 'loser'])
                      ->default('active')
                      ->after('indice');
            });
        }

        // 2. Agregar campos de auditoría (solo si no existen)
        Schema::table('card_raffle', function (Blueprint $table) {
            if (!Schema::hasColumn('card_raffle', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('status');
            }
            
            if (!Schema::hasColumn('card_raffle', 'cancelled_reason')) {
                $table->string('cancelled_reason', 255)->nullable()->after('cancelled_at');
            }
            
            // Agregar índices
            $table->index('status', 'idx_card_raffle_status');
            $table->index('cancelled_at', 'idx_card_raffle_cancelled_at');
            $table->index(['raffle_id', 'status'], 'idx_raffle_status');
            $table->index(['user_id', 'status'], 'idx_user_status');
        });

        // 3. Si existe la columna active, eliminarla
        if (Schema::hasColumn('card_raffle', 'active')) {
            Schema::table('card_raffle', function (Blueprint $table) {
                $table->dropColumn('active');
            });
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::table('card_raffle', function (Blueprint $table) {
            // Eliminar índices
            $table->dropIndex('idx_card_raffle_status');
            $table->dropIndex('idx_card_raffle_cancelled_at');
            $table->dropIndex('idx_raffle_status');
            $table->dropIndex('idx_user_status');
            
            // Eliminar columnas
            $table->dropColumn('status');
            $table->dropColumn('cancelled_at');
            $table->dropColumn('cancelled_reason');
            
            // Restaurar active
            $table->string('active', 2)->nullable()->default('1');
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}