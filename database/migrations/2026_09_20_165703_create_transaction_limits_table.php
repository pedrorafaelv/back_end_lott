<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transaction_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('currency_code', 10);
            $table->decimal('max_deposit_per_transaction', 12, 2)->default(1000);
            $table->decimal('max_deposit_per_day', 12, 2)->default(5000);
            $table->decimal('max_withdrawal_per_transaction', 12, 2)->default(500);
            $table->decimal('max_withdrawal_per_day', 12, 2)->default(2000);
            $table->decimal('withdrawal_commission_percent', 5, 2)->default(3.00);
            $table->decimal('withdrawal_commission_fixed', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'currency_code']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('transaction_limits');
    }
};