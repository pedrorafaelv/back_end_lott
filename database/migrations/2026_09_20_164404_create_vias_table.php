<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vias', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('label');
            $table->string('icon')->nullable();
            $table->string('color', 20)->nullable();
            $table->enum('type', ['credit', 'debit', 'neutral']);
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        // Datos iniciales
        DB::table('vias')->insert([
            ['code' => 'transfer',   'label' => 'Transferencia',     'icon' => 'fas fa-exchange-alt', 'color' => '#3a7ebf', 'type' => 'neutral', 'display_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'award',      'label' => 'Premio',            'icon' => 'fas fa-trophy',       'color' => '#ffd700', 'type' => 'credit',  'display_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'bet',        'label' => 'Apuesta',           'icon' => 'fas fa-dice',         'color' => '#ff4757', 'type' => 'debit',   'display_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'promotion',  'label' => 'Promoción',         'icon' => 'fas fa-gift',         'color' => '#00e676', 'type' => 'credit',  'display_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'other',      'label' => 'Otro',              'icon' => 'fas fa-circle',       'color' => '#909090', 'type' => 'neutral', 'display_order' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'deposit',    'label' => 'Depósito',          'icon' => 'fas fa-arrow-down',   'color' => '#00e676', 'type' => 'credit',  'display_order' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'withdrawal', 'label' => 'Retiro',            'icon' => 'fas fa-arrow-up',     'color' => '#ff6b6b', 'type' => 'debit',   'display_order' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'commission', 'label' => 'Comisión',          'icon' => 'fas fa-percent',      'color' => '#f39c12', 'type' => 'debit',   'display_order' => 8, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('vias');
    }
};