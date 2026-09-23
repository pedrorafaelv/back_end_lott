<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('level_user', function (Blueprint $table) {
            // Snapshot de stats al momento de asignar el nivel
            $table->unsignedInteger('games_played')->default(0)->after('user_id');
            $table->unsignedInteger('wins')->default(0)->after('games_played');
            $table->unsignedInteger('active_raffles')->default(0)->after('wins');
            $table->unsignedInteger('total_raffles')->default(0)->after('active_raffles');
            $table->decimal('total_prizes', 15, 2)->default(0)->after('total_raffles');

            // Flags
            $table->boolean('is_current')->default(true)->after('total_prizes');
            $table->timestamp('assigned_at')->nullable()->after('is_current');
            $table->text('notes')->nullable()->after('assigned_at');

            // Índices
            $table->index(['user_id', 'is_current']);
            $table->index('level_id');
        });
    }

    public function down(): void
    {
        Schema::table('level_user', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_current']);
            $table->dropIndex(['level_id']);
            $table->dropColumn([
                'games_played',
                'wins',
                'active_raffles',
                'total_raffles',
                'total_prizes',
                'is_current',
                'assigned_at',
                'notes',
            ]);
        });
    }
};