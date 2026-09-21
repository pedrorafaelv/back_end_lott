<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->string('symbol', 10);
            $table->integer('decimals')->default(2);
            $table->string('flag', 10)->nullable();
            $table->boolean('is_crypto')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        // Datos iniciales
        DB::table('currencies')->insert([
            ['code' => 'USD',  'name' => 'Dólar Estadounidense', 'symbol' => '$',  'decimals' => 2, 'flag' => 'us', 'display_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'EUR',  'name' => 'Euro',                 'symbol' => '€',  'decimals' => 2, 'flag' => 'eu', 'display_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'VES',  'name' => 'Bolívar',              'symbol' => 'Bs', 'decimals' => 2, 'flag' => 've', 'display_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'USDT', 'name' => 'Tether',               'symbol' => '₮',  'decimals' => 2, 'is_crypto' => true, 'display_order' => 4, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('currencies');
    }
};