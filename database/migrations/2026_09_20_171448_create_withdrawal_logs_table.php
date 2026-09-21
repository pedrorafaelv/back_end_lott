<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('withdrawal_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('withdrawal_request_id')->constrained('withdrawal_requests')->onDelete('cascade');
            $table->foreignId('admin_id')->nullable()->constrained('users');
            $table->string('action', 50);      // 'created', 'approved', 'rejected', 'completed', 'cancelled'
            $table->text('notes')->nullable();
            $table->json('data')->nullable();  // Datos adicionales
            $table->timestamps();

            $table->index(['withdrawal_request_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('withdrawal_logs');
    }
};