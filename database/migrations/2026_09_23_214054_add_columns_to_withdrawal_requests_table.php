<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            // Cuenta destino (FK a accounts)
            if (!Schema::hasColumn('withdrawal_requests', 'account_id')) {
                $table->foreignId('account_id')->nullable()->after('user_id')
                      ->constrained('accounts')->nullOnDelete();
            }

            // Auditoría de rechazo
            if (!Schema::hasColumn('withdrawal_requests', 'rejected_by')) {
                $table->foreignId('rejected_by')->nullable()->after('approved_by')
                      ->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('withdrawal_requests', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('approved_at');
            }
            if (!Schema::hasColumn('withdrawal_requests', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('admin_notes');
            }

            // Auditoría de completado
            if (!Schema::hasColumn('withdrawal_requests', 'completed_by')) {
                $table->foreignId('completed_by')->nullable()->after('rejected_by')
                      ->constrained('users')->nullOnDelete();
            }

            // Referencia de transferencia
            if (!Schema::hasColumn('withdrawal_requests', 'transaction_reference')) {
                $table->string('transaction_reference', 100)->nullable()->after('payment_data');
            }
        });
    }

    public function down(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropForeign(['rejected_by']);
            $table->dropForeign(['completed_by']);

            $table->dropColumn([
                'account_id',
                'rejected_by',
                'rejected_at',
                'rejection_reason',
                'completed_by',
                'transaction_reference',
            ]);
        });
    }
};