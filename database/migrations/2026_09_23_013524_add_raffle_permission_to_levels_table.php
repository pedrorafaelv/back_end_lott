<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('levels', function (Blueprint $table) {
            // Identificación visual
            $table->string('slug', 50)->unique()->after('name');
            $table->string('icon', 50)->nullable()->after('description');
            $table->string('color', 20)->nullable()->after('icon');

            // Límites de sorteos
            $table->unsignedInteger('max_raffles_active')->default(0)->after('color');
            $table->decimal('max_amount', 15, 2)->default(0)->after('max_raffles_active');
            $table->unsignedTinyInteger('max_retention_percent')->default(0)->after('max_amount');

            // Permisos booleanos
            $table->boolean('can_create_private')->default(false)->after('max_retention_percent');
            $table->boolean('can_use_auto_type')->default(false)->after('can_create_private');
            $table->boolean('can_use_custom_fichas')->default(false)->after('can_use_auto_type');

            // Requisitos para alcanzar el nivel
            $table->unsignedInteger('min_games_played')->default(0)->after('can_use_custom_fichas');
            $table->unsignedInteger('min_days_registered')->default(0)->after('min_games_played');
            $table->unsignedInteger('min_wins')->default(0)->after('min_days_registered');

            // Orden de visualización
            $table->unsignedTinyInteger('order')->default(0)->after('min_wins');
        });
    }

    public function down(): void
    {
        Schema::table('levels', function (Blueprint $table) {
            $table->dropColumn([
                'slug',
                'icon',
                'color',
                'max_raffles_active',
                'max_amount',
                'max_retention_percent',
                'can_create_private',
                'can_use_auto_type',
                'can_use_custom_fichas',
                'min_games_played',
                'min_days_registered',
                'min_wins',
                'order',
            ]);
        });
    }
};