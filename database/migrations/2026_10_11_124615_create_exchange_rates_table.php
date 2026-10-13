<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExchangeRatesTable extends Migration
{
    public function up()
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('currency_from', 10)->default('USD');
            $table->string('currency_to', 10)->default('VES');
            $table->decimal('rate', 15, 4);
            $table->date('value_date')->unique(); // Fecha valor del BCV
            $table->timestamp('scraped_at');      // Cuándo se obtuvo
            $table->string('source')->default('bcv');
            $table->timestamps();
            
            $table->index(['currency_from', 'currency_to', 'value_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('exchange_rates');
    }
}