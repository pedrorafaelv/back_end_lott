<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up()
    {
        Schema::create('legal_document_versions', function (Blueprint $table) {
            $table->id();
            $table->string('doc_type', 50);            // 'terms', 'privacy', 'cookies'
            $table->string('version', 20);             // '1.0', '2.0'
            $table->timestamp('effective_date');     // Cuándo entra en vigor
            $table->string('content_hash', 64);        // SHA-256 del texto
            $table->longText('content_text');          // Texto íntegro (inmutable)
            $table->timestamps();

            $table->unique(['doc_type', 'version']);
            $table->index(['doc_type', 'effective_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_document_versions');
    }
};