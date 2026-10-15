<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terms_acceptance_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->foreignId('doc_version_id')
                  ->constrained('legal_document_versions');
            $table->timestamp('accepted_at')->useCurrent();

            // Datos técnicos para prueba forense
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('source_url')->nullable();

            // Control de re-aceptación: NULL = aceptación vigente
            $table->foreignId('superseded_by')
                  ->nullable()
                  ->constrained('terms_acceptance_log')
                  ->nullOnDelete();

            $table->timestamps();

            $table->index('user_id');
            $table->index('accepted_at');
            $table->index('doc_version_id');
        });

        // Índice parcial: solo UNA aceptación vigente por usuario y tipo de documento.
        // PostgreSQL lo soporta nativamente; en MySQL no existe índice parcial,
        // se puede simular con una columna generada o validación en aplicación.
        // DB::statement('
        //     CREATE UNIQUE INDEX idx_terms_vigente
        //     ON terms_acceptance_log (user_id, doc_version_id)
        //     WHERE superseded_by IS NULL
        // ');
    }

    public function down(): void
    {
        // DB::statement('DROP INDEX IF EXISTS idx_terms_vigente');
        Schema::dropIfExists('terms_acceptance_log');
    }
};