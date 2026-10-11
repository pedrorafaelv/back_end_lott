<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('groupfichas', function (Blueprint $table) {
            $table->string('urlImagen', 500)
                  ->nullable()
                  ->after('description');  // 👈 ajusta según tu tabla

            $table->string('urlImagenAlt', 255)   // 👈 segundo campo
                  ->nullable()
                  ->after('urlImagen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groupfichas', function (Blueprint $table) {
            $table->dropColumn(['urlImagen', 'urlImagenAlt']);
        });
    }
};