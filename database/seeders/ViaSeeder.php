<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ViaSeeder extends Seeder
{
    /**
     * Vías de entrada/salida de valores en cuentas de usuario.
     *
     * type:
     *   - credit   → solo suma saldo
     *   - debit    → solo resta saldo
     *   - neutral  → puede sumar o restar según contexto
     */
    public function run(): void
    {
        $now = now();

        $vias = [
            // ==================== ENTRADAS DE DINERO ====================
            [
                'code'          => 'deposit',
                'label'         => 'Depósito',
                'icon'          => 'fas fa-arrow-down',
                'color'         => '#00e676',
                'type'          => 'credit',
                'is_active'     => true,
                'display_order' => 1,
            ],
            [
                'code'          => 'award',
                'label'         => 'Premio',
                'icon'          => 'fas fa-trophy',
                'color'         => '#ffd700',
                'type'          => 'credit',
                'is_active'     => true,
                'display_order' => 2,
            ],
            [
                'code'          => 'bonus',
                'label'         => 'Regalía',
                'icon'          => 'fas fa-gift',
                'color'         => '#00e676',
                'type'          => 'credit',
                'is_active'     => true,
                'display_order' => 3,
            ],
            [
                'code'          => 'promotion',
                'label'         => 'Promoción',
                'icon'          => 'fas fa-bullhorn',
                'color'         => '#9b59b6',
                'type'          => 'credit',
                'is_active'     => true,
                'display_order' => 4,
            ],
            [
                'code'          => 'refund',
                'label'         => 'Reembolso',
                'icon'          => 'fas fa-undo',
                'color'         => '#3498db',
                'type'          => 'credit',
                'is_active'     => true,
                'display_order' => 5,
            ],
            [
                'code'          => 'unblock',
                'label'         => 'Desbloqueo',
                'icon'          => 'fas fa-unlock',
                'color'         => '#1abc9c',
                'type'          => 'credit',
                'is_active'     => true,
                'display_order' => 6,
            ],

            // ==================== SALIDAS DE DINERO ====================
            [
                'code'          => 'withdrawal',
                'label'         => 'Retiro',
                'icon'          => 'fas fa-arrow-up',
                'color'         => '#ff6b6b',
                'type'          => 'debit',
                'is_active'     => true,
                'display_order' => 7,
            ],
            [
                'code'          => 'bet',
                'label'         => 'Apuesta',
                'icon'          => 'fas fa-dice',
                'color'         => '#ff4757',
                'type'          => 'debit',
                'is_active'     => true,
                'display_order' => 8,
            ],
            [
                'code'          => 'commission',
                'label'         => 'Comisión',
                'icon'          => 'fas fa-percent',
                'color'         => '#f39c12',
                'type'          => 'debit',
                'is_active'     => true,
                'display_order' => 9,
            ],
            [
                'code'          => 'block',
                'label'         => 'Bloqueo',
                'icon'          => 'fas fa-lock',
                'color'         => '#e74c3c',
                'type'          => 'debit',
                'is_active'     => true,
                'display_order' => 10,
            ],

            // ==================== NEUTRALES (entrada o salida) ====================
            [
                'code'          => 'transfer',
                'label'         => 'Transferencia',
                'icon'          => 'fas fa-exchange-alt',
                'color'         => '#3a7ebf',
                'type'          => 'neutral',
                'is_active'     => true,
                'display_order' => 11,
            ],
            [
                'code'          => 'mobile_payment',
                'label'         => 'Pago Móvil',
                'icon'          => 'fas fa-mobile-alt',
                'color'         => '#2ecc71',
                'type'          => 'neutral',
                'is_active'     => true,
                'display_order' => 12,
            ],
            [
                'code'          => 'adjustment',
                'label'         => 'Ajuste',
                'icon'          => 'fas fa-sliders-h',
                'color'         => '#95a5a6',
                'type'          => 'neutral',
                'is_active'     => true,
                'display_order' => 13,
            ],
            [
                'code'          => 'other',
                'label'         => 'Otro',
                'icon'          => 'fas fa-circle',
                'color'         => '#909090',
                'type'          => 'neutral',
                'is_active'     => true,
                'display_order' => 14,
            ],
        ];

        // Añadir timestamps y hacer upsert por 'code'
        $rows = array_map(fn ($v) => $v + [
            'created_at' => $now,
            'updated_at' => $now,
        ], $vias);

        DB::table('vias')->upsert(
            $rows,
            ['code'],                                        // unique key
            ['label', 'icon', 'color', 'type', 'is_active', 'display_order', 'updated_at']
        );
    }
}